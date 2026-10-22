const test = require("node:test");
const assert = require("node:assert/strict");
const fs = require("node:fs");
const vm = require("node:vm");
const path = require("node:path");

const source = fs.readFileSync(path.join(__dirname, "../../assets/js/webmcp.js"), "utf8");

function loadModule(options = {}) {
  const registered = [];
  const warnings = [];
  const window = {
    location: { href: "https://senoobar.ir/shop/" },
    SenoobarWebMCPConfig: {
      storeApiBase: "https://senoobar.ir/wp-json/wc/store/v1/",
      storeName: "صنوبر",
      homeUrl: "https://senoobar.ir/",
      shopUrl: "https://senoobar.ir/shop/",
      contactUrl: ""
    },
    console: { warn: (...args) => warnings.push(args) },
    fetch: async () => ({ ok: true, json: async () => [] })
  };
  const document = options.noWebMCP ? {} : {
    modelContext: {
      registerTool: async tool => { registered.push(tool); }
    }
  };
  vm.runInNewContext(source, { window, document, URL, Number, Object, Array, String, TypeError, Error, RegExp, Promise });
  return { window, registered, warnings };
}

test("strict schemas disallow additional properties and bound inputs", () => {
  const { window } = loadModule({ noWebMCP: true });
  const schemas = window.SenoobarWebMCPInternals.schemas;
  assert.equal(schemas.search.additionalProperties, false);
  assert.equal(schemas.product.additionalProperties, false);
  assert.equal(schemas.store.additionalProperties, false);
  assert.equal(schemas.search.properties.query.maxLength, 100);
  assert.equal(schemas.search.properties.limit.maximum, 5);
});

test("search input trims whitespace and applies a safe default limit", () => {
  const { window } = loadModule({ noWebMCP: true });
  assert.deepEqual(
    JSON.parse(JSON.stringify(window.SenoobarWebMCPInternals.validateSearchInput({ query: "  تخت خواب  " }))),
    { query: "تخت خواب", limit: 5 }
  );
});

test("invalid search inputs and extra keys are rejected", () => {
  const { window } = loadModule({ noWebMCP: true });
  const validate = window.SenoobarWebMCPInternals.validateSearchInput;
  assert.throws(() => validate({ query: " " }), /بین ۱ تا ۱۰۰/);
  assert.throws(() => validate({ query: "x".repeat(101) }), /بین ۱ تا ۱۰۰/);
  assert.throws(() => validate({ query: "bed", limit: 6 }), /بین ۱ تا ۵/);
  assert.throws(() => validate({ query: "bed", hidden: true }), /پشتیبانی‌نشده/);
  assert.throws(() => validate(null), /شیء/);
});

test("product IDs must be positive safe integers and reject extra fields", () => {
  const { window } = loadModule({ noWebMCP: true });
  const validate = window.SenoobarWebMCPInternals.validateProductInput;
  assert.deepEqual(JSON.parse(JSON.stringify(validate({ id: 12 }))), { id: 12 });
  assert.throws(() => validate({ id: 0 }), /مثبت/);
  assert.throws(() => validate({ id: "12" }), /مثبت/);
  assert.throws(() => validate({ id: 12, url: "https://evil.example" }), /پشتیبانی‌نشده/);
});

test("API URLs are same-origin and query values are encoded", () => {
  const { window } = loadModule({ noWebMCP: true });
  const build = window.SenoobarWebMCPInternals.buildApiUrl;
  const url = new URL(build("products", { search: "تخت خواب", per_page: 5 }));
  assert.equal(url.origin, "https://senoobar.ir");
  assert.equal(url.pathname, "/wp-json/wc/store/v1/products");
  assert.equal(url.searchParams.get("search"), "تخت خواب");
  window.SenoobarWebMCPConfig.storeApiBase = "https://evil.example/wp-json/wc/store/v1/";
  assert.throws(() => build("products", {}), /هم‌مبدأ/);
});

test("unsupported browsers do not throw or register tools", () => {
  const { registered, window } = loadModule({ noWebMCP: true });
  assert.equal(registered.length, 0);
  assert.ok(window.SenoobarWebMCPInternals);
});

test("compatible browsers register exactly three read-only tools", async () => {
  const { registered, warnings } = loadModule();
  await new Promise(resolve => setImmediate(resolve));
  assert.deepEqual(registered.map(tool => tool.name), [
    "senoobar_search_products",
    "senoobar_get_product_details",
    "senoobar_get_store_info"
  ]);
  assert.ok(registered.every(tool => tool.annotations.readOnlyHint === true));
  assert.ok(registered.every(tool => tool.inputSchema.additionalProperties === false));
  assert.equal(warnings.length, 0);
});

test("store info rejects parameters and exposes only public links", () => {
  const { window } = loadModule({ noWebMCP: true });
  const getInfo = window.SenoobarWebMCPInternals.getStoreInfo;
  assert.equal(getInfo({}).name, "صنوبر");
  assert.throws(() => getInfo({ customerId: 1 }), /پارامتر ورودی/);
});

test("product summary returns bounded public fields only", () => {
  const { window } = loadModule({ noWebMCP: true });
  const summary = window.SenoobarWebMCPInternals.productSummary({
    id: 4,
    name: "تخت خواب",
    permalink: "https://senoobar.ir/product/bed/",
    description: "private field should be ignored",
    short_description: "<p>توضیح&nbsp;کوتاه</p>",
    prices: { price: "120000", currency_code: "IRR", currency_symbol: "ریال" },
    is_in_stock: true,
    images: [{ src: "https://senoobar.ir/image.webp", alt: "تصویر" }],
    customer_data: { email: "secret@example.com" }
  });
  assert.equal(summary.id, 4);
  assert.equal(summary.description, "توضیح کوتاه");
  assert.equal(summary.inStock, true);
  assert.equal("customer_data" in summary, false);
  assert.equal("sku" in summary, false);
});

test("public search forms are annotated while sensitive forms are not", () => {
  const header = fs.readFileSync(path.join(__dirname, "../../header.php"), "utf8");
  assert.equal((header.match(/toolname="senoobar_search_form_/g) || []).length, 2);
  assert.equal((header.match(/toolparam="s"/g) || []).length, 2);
  assert.doesNotMatch(header, /class="mobile-newsletter-form"[^>]*toolname=/);
  assert.doesNotMatch(header, /checkout[^>]*toolname=/i);
  assert.doesNotMatch(header, /newsletter[^>]*toolname=/i);
});

test("tool schemas are object schemas with explicit closed inputs", () => {
  const { window } = loadModule({ noWebMCP: true });
  for (const schema of Object.values(window.SenoobarWebMCPInternals.schemas)) {
    assert.equal(schema.type, "object");
    assert.equal(schema.additionalProperties, false);
    assert.ok(schema.properties && typeof schema.properties === "object");
    assert.ok(Array.isArray(schema.required) || schema === window.SenoobarWebMCPInternals.schemas.store);
    for (const key of schema.required || []) assert.ok(key in schema.properties);
  }
});

test("loading the module twice does not register duplicate tools", async () => {
  const { window, registered } = loadModule();
  await new Promise(resolve => setImmediate(resolve));
  vm.runInNewContext(source, {
    window,
    document: { modelContext: { registerTool: async tool => { registered.push(tool); } } },
    URL, Number, Object, Array, String, TypeError, Error, RegExp, Promise
  });
  await new Promise(resolve => setImmediate(resolve));
  assert.equal(registered.length, 3);
});
