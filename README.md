# Matchory Laravel Template

A minimal, opinionated starting point for new Matchory Laravel projects. It is a runnable Laravel 13
application with formatting, static analysis, and testing already wired up against
[`matchory/coding-style`](https://github.com/matchory/coding-style), so a new project starts with the
same conventions as the rest of the codebase instead of drifting from day one.

## Getting started

Create a new repository from this template, then set up the project locally:

```bash
gh repo create your-project --template matchory/template-laravel --private --clone
cd your-project
composer install
cp .env.example .env
php artisan key:generate
```

From there, run `composer test` to confirm the default Pest suite passes and `composer style:verify`
to confirm the style tooling is correctly wired up.

## What's wired up, and why

**Pint formats via `--config`, not a merged configuration.** Pint can only load a single
configuration file per run; it has no mechanism for merging a shared base with local overrides. The
`fmt` script points `--config` directly at the ruleset shipped in `matchory/coding-style`, which is
why there is no local `pint.json`: adding one would silently take over from the package's config
instead of extending it.

**PHPStan's rulesets are included, its extensions auto-discovered.** `phpstan.neon` pulls in the
package's Laravel, Pest, and complexity rulesets under `includes:`. `matchory/coding-style` also ships
a `base.neon` of settings safe for any consumer, pulled in via the package's own auto-discovered
`extension.neon`; `phpstan/extension-installer` includes it automatically the moment the package is
installed, so it must never be included by hand.

**`cognitive_complexity` is declared locally, on purpose.** PHPStan resolves configuration by merging
every auto-discovered extension's config after the `includes:` list but before this project's own
`parameters:` block, and `phpstan/extension-installer` orders those extension configs alphabetically
by package name, not by anything a consumer controls such as require order or install time. Because
`tomasvotruba/cognitive-complexity` sorts after `matchory/coding-style`, its own defaults would
silently win over whatever threshold the shared package tries to set, so the threshold has to be
restated locally to take effect. `phpstan.neon` keeps `level`, `paths`, and `cognitive_complexity`
local for this reason; everything else comes from the shared package.

**Rector builds on the shared Laravel preset,** extended with this project's own paths and a cache
directory, in `rector.php`.

**`composer style:verify` is the acceptance test.** It doesn't just check that the style package is a
dependency; it checks that this repository actually consumes it the way it's meant to be consumed. CI
runs it on every push, and it's worth running locally after touching any of the files above.

## Commands

```bash
composer fmt           # format the codebase
composer fmt:test      # check formatting without changing files
composer analyze       # run PHPStan
composer rector        # apply Rector refactorings
composer test          # run the Pest suite
composer style:verify  # verify the style tooling is correctly wired up
```

## What this template deliberately doesn't include

This is a starting point, not a scaffold for a specific application. It has no `config/` directory,
no `database/` migrations or seeders, and no domain code: those are exactly the things a real project
adds first. See `CLAUDE.md` for the conventions to follow once you do.
