# WebMCP for Senoobar — Design Specification

## Status
Design proposal for review. No production code is included in this commit.

## Goals
- Expose useful, public, read-only store capabilities to compatible browser agents.
- Make eligible public forms discoverable where supported by the WebMCP implementation.
- Keep the theme functional when `document.modelContext` is absent or registration fails.
- Validate tool input schemas and ensure every tool returns JSON-serializable, minimal results.
- Add automated checks without adding background workers, third-party runtime dependencies, REST endpoints, or persistent network activity.

## Repository context
The theme is a PHP WordPress/WooCommerce theme. `functions.php` loads theme modules; `Senoobar_Theme::assets()` is the existing client asset enqueue point. The desktop header search form is in `header.php`, product search results are handled by `search.php`, and product details are rendered by `woocommerce/single-product.php`. Existing CI runs PHP syntax checks and builds a theme ZIP.

## Proposed architecture
1. Add a small dedicated client module (for example `assets/js/webmcp.js`) and enqueue it only on public front-end pages. Do not load it in wp-admin. It must feature-detect `document.modelContext.registerTool`, register once per document, and gracefully no-op when unsupported.
2. Add a small PHP helper module (for example `inc/webmcp.php`) for safe public data/config and eligible form annotations, loaded from `functions.php`. Keep the existing search form action and behavior unchanged.
3. Register only read-only imperative tools:
   - `senoobar_search_products`: accept a bounded query string and optional bounded result count; navigate to the existing product search URL or return concise public results using existing rendered/store data. No custom public REST endpoint.
   - `senoobar_get_product_details`: accept a product URL/ID only when it resolves to a published, publicly viewable product; return name, canonical URL, public description excerpt, SKU only if public policy allows it, price, currency, availability, and product image URLs.
   - `senoobar_get_store_info`: return public site name, canonical homepage, shop URL, and public contact links if configured.
4. Use strict JSON Schema objects: `additionalProperties: false`, explicit required fields, bounded string lengths and numeric limits. Treat all input as untrusted. Resolve IDs/URLs server-side only through WordPress/WooCommerce data already available; do not fetch arbitrary URLs.
5. Form coverage: annotate only public search forms using the currently supported form metadata attributes when the relevant WebMCP implementation recognizes them. Do not mark login, account, cart, checkout, payment, newsletter, or other state-changing forms as agent-completable. Do not change their submission behavior.
6. Keep all tool descriptions explicit about read-only behavior. No add-to-cart, checkout, order creation, login, personal data, wishlist mutation, or payment tools.
7. Add a small dependency-free automated test suite for the registration module and schema definitions, plus CI checks for PHP syntax and JS/schema tests. Tests must cover unsupported browsers, one-time registration, valid/invalid inputs, malformed input, and exclusion of sensitive forms. Prefer existing project conventions and avoid changing unrelated theme behavior.

## Performance and security constraints
- No remote scripts, analytics calls, workers, timers, or background polling.
- Do not execute tools until explicitly invoked by the agent.
- Never return customer/order/session data, nonce values, authentication state, or private product drafts.
- Escape output in PHP; validate/normalize all tool input; cap result counts and response size.
- Do not modify `main`, deployment settings, the live site, or unrelated theme files.

## Acceptance criteria
- Compatible browser: all intended tools register once and their schemas validate.
- Unsupported browser or registration error: no uncaught exception and normal theme functionality remains unchanged.
- Public product search/details/store-info calls return bounded, JSON-serializable data; invalid IDs, drafts, unsupported input, and extra fields are rejected safely.
- Public search form is discoverable where the target browser supports declarative form metadata; sensitive/stateful forms remain excluded.
- Automated tests and existing PHP/ZIP CI pass.
- Final report distinguishes automated test evidence from a live-browser WebMCP audit; do not claim the three audit checks pass unless actually run in a compatible browser.

## Files expected to change during implementation
- `functions.php` (load helper module)
- `inc/webmcp.php` (safe form annotations and server-side public data helpers if needed)
- `assets/js/webmcp.js` (imperative registration and runtime validation)
- `header.php` (search form metadata only, if supported)
- `tests/webmcp/*` (automated tests)
- `.github/workflows/theme-ci.yml` (run tests)

No implementation changes should begin until this written design is reviewed and approved.
