(function (window, document) {
  "use strict";

  var config = window.SenoobarWebMCPConfig || {};
  var MAX_QUERY_LENGTH = 100;
  var MAX_RESULTS = 5;
  var status = window.SenoobarWebMCPStatus || { supported: false, ready: false, registeredTools: [], errors: [], reason: "" };
  window.SenoobarWebMCPStatus = status;
  status.expectedTools = ["senoobar_search_products", "senoobar_get_product_details", "senoobar_get_store_info"];

  var schemas = {
    search: {
      type: "object",
      properties: {
        query: { type: "string", minLength: 1, maxLength: MAX_QUERY_LENGTH, description: "عبارت جست‌وجوی محصول؛ حداکثر ۱۰۰ نویسه." },
        limit: { type: "integer", minimum: 1, maximum: MAX_RESULTS, description: "حداکثر تعداد نتیجه، از ۱ تا ۵." }
      },
      required: ["query"],
      additionalProperties: false
    },
    product: {
      type: "object",
      properties: {
        id: { type: "integer", minimum: 1, description: "شناسه عددی یک محصول عمومی منتشرشده." }
      },
      required: ["id"],
      additionalProperties: false
    },
    store: {
      type: "object",
      properties: {},
      additionalProperties: false
    }
  };

  function isPlainObject(value) {
    return value !== null && typeof value === "object" && (Object.getPrototypeOf(value) === Object.prototype || Object.getPrototypeOf(value) === null);
  }

  function rejectUnknownKeys(input, allowed) {
    Object.keys(input).forEach(function (key) {
      if (allowed.indexOf(key) === -1) throw new TypeError("پارامتر پشتیبانی‌نشده: " + key);
    });
  }

  function validateSearchInput(input) {
    if (!isPlainObject(input)) throw new TypeError("ورودی جست‌وجو باید یک شیء باشد.");
    rejectUnknownKeys(input, ["query", "limit"]);
    if (typeof input.query !== "string") throw new TypeError("عبارت جست‌وجو باید متن باشد.");
    var query = input.query.trim();
    if (!query || query.length > MAX_QUERY_LENGTH) throw new TypeError("عبارت جست‌وجو باید بین ۱ تا ۱۰۰ نویسه باشد.");
    var limit = input.limit === undefined ? MAX_RESULTS : input.limit;
    if (!Number.isInteger(limit) || limit < 1 || limit > MAX_RESULTS) throw new TypeError("تعداد نتایج باید عدد صحیحی بین ۱ تا ۵ باشد.");
    return { query: query, limit: limit };
  }

  function validateProductInput(input) {
    if (!isPlainObject(input)) throw new TypeError("ورودی محصول باید یک شیء باشد.");
    rejectUnknownKeys(input, ["id"]);
    if (!Number.isSafeInteger(input.id) || input.id < 1) throw new TypeError("شناسه محصول باید عدد صحیح مثبت باشد.");
    return { id: input.id };
  }

  function buildApiUrl(path, params) {
    if (typeof config.storeApiBase !== "string" || !config.storeApiBase) throw new Error("WooCommerce Store API پیکربندی نشده است.");
    var current = new URL(window.location.href);
    var base = new URL(config.storeApiBase, current.href);
    if (base.origin !== current.origin) throw new Error("آدرس Store API باید هم‌مبدأ با فروشگاه باشد.");
    if (!base.pathname.endsWith("/")) base.pathname += "/";
    var safePath = path === "products" || (typeof path === "string" && /^products\/[1-9]\d*$/.test(path));
    if (!safePath) throw new Error("مسیر API مجاز نیست.");
    var target = new URL(path, base.href);
    if (target.origin !== base.origin || target.pathname.indexOf(base.pathname) !== 0) throw new Error("مسیر API مجاز نیست.");
    Object.keys(params || {}).forEach(function (key) {
      target.searchParams.set(key, String(params[key]));
    });
    return target.href;
  }

  function cleanText(value, maxLength) {
    if (typeof value !== "string") return "";
    return value.replace(/<[^>]*>/g, " ").replace(/&nbsp;/gi, " ").replace(/&amp;/gi, "&").replace(/&quot;/gi, '"').replace(/&#39;/gi, "'").replace(/&lt;/gi, "<").replace(/&gt;/gi, ">").replace(/\s+/g, " ").trim().slice(0, maxLength);
  }

  function productSummary(product) {
    if (!product || !Number.isSafeInteger(Number(product.id)) || typeof product.name !== "string" || typeof product.permalink !== "string") return null;
    var permalink;
    try {
      permalink = new URL(product.permalink, window.location.href);
      if (permalink.origin !== new URL(window.location.href).origin || !/^https?:$/.test(permalink.protocol)) return null;
    } catch (error) { return null; }
    var images = Array.isArray(product.images) ? product.images.slice(0, 3).filter(function (image) {
      return image && typeof image.src === "string" && /^https?:\/\//i.test(image.src);
    }).map(function (image) {
      return { url: image.src.slice(0, 1000), alt: cleanText(image.alt || "", 160) };
    }) : [];
    var prices = product.prices && typeof product.prices === "object" ? product.prices : {};
    return {
      id: Number(product.id),
      name: cleanText(product.name, 200),
      url: permalink.href.slice(0, 1000),
      description: cleanText(product.short_description || "", 500),
      price: typeof prices.price === "string" ? prices.price.slice(0, 32) : null,
      currencyCode: typeof prices.currency_code === "string" ? prices.currency_code.slice(0, 8) : null,
      currencySymbol: typeof prices.currency_symbol === "string" ? cleanText(prices.currency_symbol, 8) : null,
      currencyMinorUnit: Number.isInteger(prices.currency_minor_unit) ? prices.currency_minor_unit : null,
      inStock: typeof product.is_in_stock === "boolean" ? product.is_in_stock : null,
      images: images
    };
  }

  async function fetchJson(path, params, signal) {
    var url = buildApiUrl(path, params);
    var response = await window.fetch(url, {
      method: "GET",
      credentials: "same-origin",
      headers: { Accept: "application/json" },
      signal: signal
    });
    if (!response || !response.ok) {
      if (response && response.status === 404) throw new Error("محصول عمومی موردنظر پیدا نشد.");
      throw new Error("در حال حاضر دریافت اطلاعات عمومی فروشگاه ممکن نیست.");
    }
    return response.json();
  }

  async function searchProducts(input, options) {
    var valid = validateSearchInput(input);
    var signal = options && options.signal;
    var data = await fetchJson("products", { search: valid.query, per_page: valid.limit, page: 1 }, signal);
    if (!Array.isArray(data)) throw new Error("پاسخ جست‌وجوی فروشگاه معتبر نیست.");
    return { ok: true, count: Math.min(data.length, valid.limit), products: data.slice(0, valid.limit).map(productSummary).filter(Boolean) };
  }

  async function getProduct(input, options) {
    var valid = validateProductInput(input);
    var data = await fetchJson("products/" + valid.id, {}, options && options.signal);
    var summary = productSummary(data);
    if (!summary) throw new Error("اطلاعات عمومی محصول معتبر نیست.");
    return { ok: true, product: summary };
  }

  function getStoreInfo(input) {
    if (!isPlainObject(input) || Object.keys(input).length) throw new TypeError("این ابزار پارامتر ورودی نمی‌پذیرد.");
    return {
      ok: true,
      name: cleanText(config.storeName || "", 160),
      homepage: typeof config.homeUrl === "string" ? config.homeUrl.slice(0, 1000) : "",
      shopUrl: typeof config.shopUrl === "string" ? config.shopUrl.slice(0, 1000) : "",
      contactUrl: typeof config.contactUrl === "string" ? config.contactUrl.slice(0, 1000) : ""
    };
  }

  var internals = {
    schemas: schemas,
    validateSearchInput: validateSearchInput,
    validateProductInput: validateProductInput,
    buildApiUrl: buildApiUrl,
    productSummary: productSummary,
    getStoreInfo: getStoreInfo
  };
  window.SenoobarWebMCPInternals = internals;

  async function registerTools() {
    var context = document && document.modelContext;
    if (!context && window.navigator) context = window.navigator.modelContext;
    if (!context || typeof context.registerTool !== "function") {
      status.supported = false;
      status.ready = false;
      status.reason = context ? "registerTool is unavailable" : "WebMCP ModelContext API is unavailable in this browser";
      return;
    }
    status.supported = true;
    status.reason = "";
    status.errors = [];
    if (!Array.isArray(status.registeredTools)) status.registeredTools = [];
    var tools = [
      {
        name: "senoobar_search_products",
        title: "جست‌وجوی محصولات صنوبر",
        description: "فقط در محصولات عمومی و منتشرشده فروشگاه صنوبر جست‌وجو می‌کند و حداکثر پنج نتیجه شامل نام، لینک، توضیح کوتاه، قیمت عمومی و موجودی برمی‌گرداند. سبد خرید یا وضعیت سفارش را تغییر نمی‌دهد.",
        inputSchema: schemas.search,
        annotations: { readOnlyHint: true, untrustedContentHint: true },
        execute: searchProducts
      },
      {
        name: "senoobar_get_product_details",
        title: "جزئیات محصول صنوبر",
        description: "اطلاعات عمومی یک محصول منتشرشده را با شناسه عددی می‌خواند. به محصولات پیش‌نویس، سفارش‌ها یا اطلاعات مشتری دسترسی ندارد و هیچ تغییری در فروشگاه ایجاد نمی‌کند.",
        inputSchema: schemas.product,
        annotations: { readOnlyHint: true, untrustedContentHint: true },
        execute: getProduct
      },
      {
        name: "senoobar_get_store_info",
        title: "اطلاعات عمومی فروشگاه صنوبر",
        description: "نام و لینک‌های عمومی فروشگاه صنوبر را برمی‌گرداند. این ابزار فقط‌خواندنی است و هیچ اطلاعات حساب یا مشتری ارائه نمی‌کند.",
        inputSchema: schemas.store,
        annotations: { readOnlyHint: true, untrustedContentHint: false },
        execute: getStoreInfo
      }
    ];
    for (var i = 0; i < tools.length; i += 1) {
      if (status.registeredTools.indexOf(tools[i].name) !== -1) continue;
      try {
        await context.registerTool(tools[i]);
        if (status.registeredTools.indexOf(tools[i].name) === -1) status.registeredTools.push(tools[i].name);
      } catch (error) {
        status.errors.push(tools[i].name);
        if (window.console && typeof window.console.warn === "function") window.console.warn("Senoobar WebMCP: registration failed for " + tools[i].name, error);
      }
    }
    status.ready = status.registeredTools.length === tools.length && status.errors.length === 0;
    if (!status.ready) status.reason = "One or more WebMCP tools failed to register";
  }

  // Prevent concurrent duplicate registration if the script is evaluated twice.
  // A later load can retry only the tools that failed during an earlier attempt.
  if (!window.__senoobarWebMCPRegistrationPromise) {
    window.__senoobarWebMCPRegistrationPromise = registerTools().finally(function () {
      window.__senoobarWebMCPRegistrationPromise = null;
    });
  }
})(typeof window !== "undefined" ? window : {}, typeof document !== "undefined" ? document : null);
