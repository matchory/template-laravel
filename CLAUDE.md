# CLAUDE.md

This template has no domain code yet, so these conventions cannot be learned from examples.
`tests/Unit/ArchTest.php` checks the ones marked (checked).

- Business logic called from a controller, command or job goes in a single-purpose
  `app/Domain/{Feature}/{Action}Action.php` with a `handle()` method. Entry points only resolve the
  action and call it. Infrastructure (API clients, token refresh, transport adapters) goes in
  `app/Support/` as plain services, not Actions.
- Input DTOs are Spatie Laravel Data classes, not Form Requests (checked). `spatie/laravel-data` is
  not installed yet; require it with the first Data class. Validate with property attributes. Use
  `rules()` only for cross-field rules on an optional property: Spatie drops rules for properties
  missing from the request.
- PATCH: type partial-update fields as `Optional` and give them no default value. A default turns a
  missing field into `null`, so "not sent" and "sent as null" look the same. Check
  `instanceof Optional` before writing the column.
- Models declare columns and relations as typed constants (`const string FIELD_NAME`,
  `const string RELATION_OWNER`). Use the constants everywhere, never the string.
- Side effects of a model change (cache, notifications, search sync) go in an observer, not in the
  controller or action.
- Authorization checks live only in policies.
- Migrations are forward-only: no `down()`, not even empty (checked). Fix a bad migration with a new one.
