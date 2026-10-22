# WebMCP for Senoobar — Design Specification

## Status
Design proposal for review. This branch currently contains only this specification; no production code has been changed.

## Goals
- Expose public, read-only store capabilities to compatible browser agents.
- Make the public product-search form discoverable where supported by WebMCP.
- Keep the theme functional when `document.modelContext` is absent or registration fails.
- Validate tool schemas and return bounded, JSON-serializable results.
- Avoid background work, third-party runtime dependencies, custom REST endpoints, and changes to the live site.

## Repository context
The theme is a PHP WordPress/WooCommerce theme. `functions.php` loads modules; `Senoobar_Theme::assets()` is the existing client asset enqueue point. The desktop header search form is in `header.php`, product search results are handled by `search.php`, and product details are rendered by `woocommerce/single-product.php`. Existing CI runs PHP syntax checks and builds an installable theme ZIP.

## Proposed architecture
1. Add a small client module such as `assets/js/webmcp.js`, enqueued only on public front-end pages, never wp-admin. It feature-detects `document.modelContext.registerTool`, registers once per document, and safely no-ops when unsupported.
2. Add a small PHP helper module such as `inc/webmcp.php` for safe public configuration and eligible form annotations, loaded from `functions.php`. Existing search behavior must remain unchanged.
3. Register these imperative tools (read-only):
   - `senoobar_search_products`: accepts a bounded search query and a small bounded result limit; queries the WooCommerce Store API public products endpoint on explicit tool invocation, returns a concise list of public products, and never navigates or changes site state.
   - `senoobar_get_product_details`: accepts a positive product ID, resolves it through the public WooCommerce Store API product endpoint, and returns only public fields such as name, canonical URL, short description, price, currency, stock status, and image URLs. Draft/private products and invalid IDs must fail safely.
   - `senoobar_get_store_info`: returns public site name, canonical homepage, shop URL, and public contact links only when configured.
4. Use strict JSON Schema objects: `additionalProperties: false`, explicit required fields, bounded string lengths, and bounded integer limits. Treat all input as untrusted. Only request same-origin, fixed WooCommerce Store API paths; never fetch arbitrary URLs. Tool invocation may perform a request, but no polling, timers, workers, or automatic background fetches are allowed.
5. Form coverage: annotate only the public product-search form using WebMCP declarative form metadata supported by the target browser. Preserve its existing action, method, field names, and behavior. Do not mark login, account, cart, checkout, payment, newsletter, or other state-changing forms as agent-completable.
6. Tool descriptions must explicitly say they are read-only. No add-to-cart, checkout, order creation, login, personal data, wishlist mutation, or payment tools.
7. Add dependency-free automated tests for registration, schema validation, input bounds, safe API URL construction, and sensitive-form exclusion. Extend existing CI to run these tests alongside its current PHP syntax and ZIP checks.

## Performance and security constraints
- No remote scripts, analytics calls, service workers, timers, or background polling.
- No tool runs until explicitly invoked by an agent.
- Never return customer/order/session data, nonce values, authentication state, or private product drafts.
- Do not trust caller-provided URLs; construct API paths from a same-origin configured base and validated numeric IDs/query values.
- Cap query length, result count, returned text and images; handle API/network errors without uncaught exceptions.
- Do not modify `main`, deployment settings, the live site, or unrelated theme behavior.

## Acceptance criteria
- On a compatible browser, all intended tools register once and each input schema validates.
- Unsupported browsers and registration/network errors leave normal theme functionality unchanged and produce no uncaught exception.
- Product search/details/store-info calls return bounded public JSON data; invalid IDs, extra fields, unsupported input, and API errors fail safely.
- Public search form is discoverable where the target browser supports declarative form metadata; sensitive/stateful forms remain excluded.
- Automated tests and existing PHP/ZIP CI pass.
- Final report distinguishes automated test evidence from a live-browser WebMCP audit. The three audit checks must not be claimed as passing unless they were actually run in a compatible browser.

## Expected implementation files
- `functions.php` — load the helper module and enqueue the script through existing theme asset patterns.
- `inc/webmcp.php` — public configuration and safe search-form metadata.
- `assets/js/webmcp.js` — tool schemas, registration, bounded Store API calls and error handling.
- `header.php` — metadata on the existing search form only.
- `tests/webmcp/*` — automated tests.
- `.github/workflows/theme-ci.yml` — run tests in CI.

Implementation begins only after this written design is reviewed and approved.
