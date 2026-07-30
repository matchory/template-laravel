# CLAUDE.md

Guidance for Claude Code (and other coding agents) working in this repository.

## Stack

- PHP 8.5, Laravel 13
- Pest 5 for testing
- PHPStan (via Larastan) at level 5, Rector, and Pint for static analysis and formatting
- Style configuration, PHPStan rulesets, and Rector presets come from `matchory/coding-style`

## Commands

```bash
composer fmt          # format the codebase with Pint
composer fmt:test      # check formatting without changing files
composer analyze       # run PHPStan
composer rector        # apply Rector refactorings
composer test           # run the Pest suite
composer style:verify   # confirm the project actually consumes the shared presets
```

CI runs the `:ci` variants of these (`analyze:ci`, `rector:ci`, `test:ci`), which add
`--no-progress`/`--dry-run`/colour flags appropriate for a non-interactive shell but otherwise do the
same checks.

## Style configuration lives elsewhere

Formatting rules, the PHPStan baseline rulesets, and the Rector preset are not defined in this
repository. They are pulled in from the `matchory/coding-style` package and referenced from three
small files:

- `composer.json`'s `fmt` script points Pint at the package's config with `--config`, because Pint
  cannot merge multiple configuration files — only one can be active, so there is no local
  `pint.json`.
- `phpstan.neon` includes the package's rulesets under `includes:`. A companion `base.neon` is added
  automatically by `phpstan/extension-installer` and must never be included by hand.
- `rector.php` builds its rule set from the package's Laravel preset.

The only style declarations that live in this repository are in `phpstan.neon`: the `level`, the
`paths` PHPStan should scan, and a `cognitive_complexity` block. That block is not optional — PHPStan
resolves configuration by merging every auto-discovered extension's config, in installation order,
after the `includes:` list but before the root file's own `parameters`. Because
`tomasvotruba/cognitive-complexity` is installed after `matchory/coding-style`, any complexity
thresholds the shared package ships are silently overwritten by the extension's defaults unless this
project restates them itself. With the local block, the resolved thresholds are `class: 50` and
`function: 15`; remove it and they quietly fall back to the package defaults of `class: 40` and
`function: 9`.

`composer style:verify` is the acceptance test for all of this: it checks that the repository is
actually wired up to consume the shared presets, not just that it depends on the package. Run it
after touching any of the three files above, and expect CI to fail if it doesn't pass.

PHPStan's `level: 5` matches the rest of the Matchory codebase. Treat it as a floor, not a target — a
greenfield project is free to raise it as the codebase matures.

## Conventions

This template ships without a domain to keep it minimal, but new code should follow these patterns
from the start.

**Domain Actions.** Business logic invoked from an entry point — an HTTP controller, a console
command, a queued job — belongs in a Domain Action: a single-purpose class named
`app/Domain/{Feature}/{Action}Action.php` with a `handle()` method. Entry points stay thin: resolve
the action via the container, call `handle()`, and return the result. Internal infrastructure
concerns that aren't themselves business logic — token refresh, third-party API clients, transport
adapters — belong in `app/Support/` as plain service classes, not as Actions.

**Data classes.** Use Spatie Laravel Data classes as input DTOs instead of Form Requests. Validate
with property attributes on the class rather than a `rules()` method, so the constraint lives next to
the property it constrains; fall back to `rules()` only for cross-field validation that involves an
optional property, since Spatie drops rules for properties that were not present in the request at
all. Data classes resolve the same way whether the entry point is HTTP or something else, so they are
reusable across controllers, jobs, and CLI commands.

**PATCH semantics.** For partial updates, use `Spatie\LaravelData\Optional` to distinguish "the field
was not sent" from "the field was sent as null". Properties that should support this must not declare
a default value — a default causes Spatie to fall back to `null` for a missing field instead of
`Optional`, collapsing the distinction. In the handler, check `instanceof Optional` before touching
the corresponding column.

**Field and relation constants.** Models declare their columns and relationships as typed constants —
`const string FIELD_NAME`, `const string RELATION_OWNER`, and so on — and refer to them by constant
everywhere a column or relation name is used: queries, factories, migrations, tests. This keeps a
rename a single-file change.

**Observers, not controllers.** Side effects that follow from a model changing — cache invalidation,
notifications, syncing to a search index — belong in an observer registered on the model, not inline
in the controller or action that triggered the change.

**Authorization only in policies.** Authorization checks belong exclusively in policy classes,
resolved through Laravel's standard authorization gate. Conditional authorization logic collapses
into a single policy ability rather than being scattered across controllers or actions.

**Migrations are forward-only.** Write only `up()`; do not add a `down()` method, not even an empty
one. Rolling back a migration is not a supported operation here — recovery from a bad migration is a
new, corrective migration, not a reverse of the old one.
