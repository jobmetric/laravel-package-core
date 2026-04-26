[contributors-shield]: https://img.shields.io/github/contributors/jobmetric/laravel-package-core.svg?style=for-the-badge
[contributors-url]: https://github.com/jobmetric/laravel-package-core/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/jobmetric/laravel-package-core.svg?style=for-the-badge&label=Fork
[forks-url]: https://github.com/jobmetric/laravel-package-core/network/members
[stars-shield]: https://img.shields.io/github/stars/jobmetric/laravel-package-core.svg?style=for-the-badge
[stars-url]: https://github.com/jobmetric/laravel-package-core/stargazers
[license-shield]: https://img.shields.io/github/license/jobmetric/laravel-package-core.svg?style=for-the-badge
[license-url]: https://github.com/jobmetric/laravel-package-core/blob/master/LICENCE.md
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-blue.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/majidmohammadian

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![MIT License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

# Laravel Package Core

**Ship Packages Faster. Keep Architecture Consistent.**

Laravel Package Core is the shared foundation behind JobMetric Laravel packages. Stop retyping the same provider wiring, CRUD scaffolding, and response boilerplate in every package and start building domain features with a single, opinionated toolkit. It provides a robust and flexible layer for package bootstrapping, container registration, reusable service patterns, dynamic model relations, resource resolution, and predictable HTTP and service responses—giving you the same structural conventions across packages without sacrificing clarity. This is where serious package development meets developer-friendly consistency—so every new package feels familiar to your team and to integrators.

## Why Laravel Package Core?

### Fluent Package Bootstrapping

Configure what your package registers and publishes—config, migrations, routes, translations, views, assets, commands, and bindings—through one provider-oriented flow instead of scattering ad hoc `mergeConfigFrom`, `loadMigrationsFrom`, and `publishes` calls across custom providers.

### Reusable CRUD Service Layer

Model domain services with query support, lifecycle hooks, event dispatching, and standardized response contracts using `AbstractCrudService`, matching patterns already proven in packages like Flow.

### Dynamic Relation Mapping

Register relations at runtime with `HasDynamicRelations` so host applications can extend package models without maintaining forks or editing vendor classes.

### Resource Resolution and Morph Attributes

Resolve context-aware resources with `ResourceResolveEvent` and attach morph-based dynamic attributes with `HasMorphResourceAttributes` for flexible APIs and admin experiences.

### Predictable Controller and Service Responses

Keep response shapes consistent with `Output\Response` and `Controllers\HasResponse`, reducing bespoke array payloads duplicated across controllers and services.

### Utilities That Match the Ecosystem

Use console scaffolding helpers, enum-oriented utilities, boolean status helpers, and global helper functions aligned with other JobMetric packages.

## What is Laravel Package Core?

Laravel Package Core is the structural glue for multi-package Laravel ecosystems: it standardizes how packages boot, bind services, publish assets, implement CRUD-style services, and return structured results.

In a typical Laravel monorepo or suite of first-party packages, teams often repeat the same infrastructure work in every provider and service. Laravel Package Core takes a different approach:

- **One bootstrap vocabulary**: Register package capabilities through shared provider patterns instead of one-off copies in each package
- **Shared CRUD conventions**: Query, hooks, events, and response contracts through `AbstractCrudService`
- **Runtime-safe extensibility**: Attach relations and resolve resources without rewriting package internals
- **Consistent responses**: Traits and value objects that keep controllers and domain services speaking the same language
- **Cross-package helpers**: Small utilities and console helpers that behave the same wherever they appear

Consider a domain package that ships migrations, config, routes, and a service consumed by host apps, while still allowing integrators to attach extra relations to your Eloquent models. With Laravel Package Core, you wire bootstrap concerns once, expose predictable service and HTTP responses, and let applications extend models at runtime. The power of a shared core lies not only in saving lines of code but also in making every package easier to onboard, review, and evolve.

## What Awaits You?

By adopting Laravel Package Core, you will:

- **Bootstrap packages consistently** - One pattern for config, routes, migrations, translations, and bindings
- **Reduce duplicated infrastructure** - Shared helpers, traits, and base services instead of copy-paste providers
- **Extend packages safely** - Dynamic relations and resource resolution without editing vendor models
- **Align responses everywhere** - Predictable shapes for controllers and domain services
- **Move faster on new packages** - Spend time on domain logic, not repeated wiring
- **Maintain clean code** - Conventions that mirror other JobMetric packages your team already uses

## Quick Start

Install Laravel Package Core via Composer:

```bash
composer require jobmetric/laravel-package-core
```

## Documentation

Ready to transform your Laravel applications? Our comprehensive documentation is your gateway to mastering Laravel Package Core:

**[📚 Read Full Documentation →](https://jobmetric.github.io/packages/laravel-package-core/)**

The documentation includes:

- **Getting Started** - Quick introduction and how the package fits the ecosystem
- **Installation** - Requirements and Composer setup
- **Showcase** - Real-world wiring from projects that already depend on this foundation
- **Package Core Service Provider** - Bootstrap capabilities, registration, and publishable resources
- **Abstract CRUD Service** - Domain services, queries, hooks, events, and responses
- **Dynamic Relations and Resources** - `HasDynamicRelations`, `ResourceResolveEvent`, and morph resource attributes
- **Controllers and Responses** - `HasResponse` and structured `Output\Response` usage
- **Utilities** - Helpers, console tools, enum patterns, and boolean status helpers

## Contributing

Thank you for participating in `laravel-package-core`. A contribution guide can be found [here](CONTRIBUTING.md).

## License

The `laravel-package-core` is open-sourced software licensed under the MIT license. See [License File](LICENCE.md) for more information.
