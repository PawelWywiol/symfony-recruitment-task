# PHPStan Setup for Symfony Project

PHPStan has been successfully added to this Symfony project for static analysis.

## What is PHPStan?

PHPStan is a static analysis tool that finds bugs in your PHP code without running it. It catches whole classes of bugs even before you write tests for the code.

## Usage

### Basic Analysis
Run PHPStan to analyze your code:
```bash
composer phpstan
```

### Generate Baseline
If you have many existing errors and want to start with a baseline:
```bash
composer phpstan:baseline
```

### Direct PHPStan Commands
You can also run PHPStan directly:
```bash
./vendor/bin/phpstan analyse
```

## Configuration

The configuration is in `phpstan.neon` file. Current settings:

- **Level**: 5 (moderate strictness, levels range from 0-9)
- **Paths**: Analyzes `src/` and `tests/` directories
- **Scan Files**: Includes `vendor/autoload.php` for proper autoloading

## Strict Types

All PHP files in this project use `declare(strict_types=1);` which:

- **Prevents type coercion**: PHP won't automatically convert types
- **Improves type safety**: Catches type-related bugs early
- **Better static analysis**: PHPStan can provide more accurate analysis
- **Modern PHP practice**: Aligns with current PHP best practices

This has already helped reduce PHPStan errors from 17 to 15 by providing more precise type information.

## Current Issues Found

PHPStan has identified several issues in your codebase:

### Controllers
- Strict comparisons that will always be false (4 errors)
- These occur because Symfony's parameter converter ensures the user is never null

### Entities
- Unused property types (1 error)
- The `$id` property type could be simplified

### Repositories
- Return type mismatches (1 error)
- The paginate method return type annotation doesn't match the actual return

### Tests
- Unreachable code statements (8 errors)
- Code after `$this->markTestIncomplete()` is unreachable

### Bootstrap
- Redundant function call (1 error)
- `method_exists()` check that always returns true

**Total: 15 errors** (reduced from 17 after adding `declare(strict_types=1);`)

## Next Steps

1. **Review the errors**: Go through each error PHPStan found and decide if they need fixing
2. **Fix critical issues**: Address the most important issues first
3. **Adjust level**: You can lower the level (e.g., to 3 or 4) if you want fewer errors initially
4. **Add extensions**: Consider adding Symfony and Doctrine extensions for better analysis

## Adding Extensions (Optional)

For better Symfony and Doctrine support, you can add these extensions:

```bash
composer require --dev phpstan/extension-installer
composer require --dev phpstan/phpstan-symfony
composer require --dev phpstan/phpstan-doctrine
```

Then update your `phpstan.neon` configuration accordingly.

## Integration with CI/CD

You can integrate PHPStan into your CI/CD pipeline by adding it to your build scripts. The tool will exit with code 1 if any errors are found, making it suitable for automated checks.

## Level Guide

- **Level 0**: Basic checks
- **Level 5**: Moderate strictness (current)
- **Level 9**: Maximum strictness

Start with a lower level and gradually increase as you fix issues.

## Security Note

**Important**: The `.env.dev` file containing sensitive information like `APP_SECRET` has been removed from git tracking and added to `.gitignore`. 

- Use `.env.dev.example` as a template for your local `.env.dev` file
- Generate a new `APP_SECRET` for your local development environment
- Never commit sensitive environment files to version control 
