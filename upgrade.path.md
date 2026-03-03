# Gary Drupal Upgrade Path

## Starting State
- Drupal 9.3.9
- PHP 7.4
- MySQL 5.7.44 (running in Docker on port 3307)
- Drush 10.4.0

## Target / Current State (as of 2026-03-01)
- Drupal 10.6.3
- PHP 8.4
- MySQL 8 (local, port 3306)
- Drush 12.5.3

---

## Phase 1: Drupal 9.3.9 → 9.5.11
**Git commit:** `5c4155c`

This intermediate step was required to ensure all D9 update hooks ran cleanly
before the D10 jump, and to avoid skipping over critical schema changes.

### Actions
1. Updated `composer.json` core constraint to `^9.5`
2. Removed the Ajax firewall patch (issue #2580191) — already fixed in D9.5 core
3. Kept all Quickedit patches in place (removed in Phase 2 before D10)
4. Ran `php vendor/bin/drush updb` — included `jsonapi_update_9401`
5. Ran `php vendor/bin/drush cr`

---

## Phase 2: Drupal 9.5.11 → 10.6.3
**Git commit:** `9af6224`

### Step 1: Switch PHP to 8.4
D10 requires PHP 8.1+. PHP 8.1, 8.3, and 8.4 are all pre-installed.

```bash
sudo update-alternatives --config php
# select PHP 8.4
```

### Step 2: Uninstall modules removed from D10 core
These modules no longer exist in D10 core and **must be uninstalled before
running composer update**, or Drupal will fail to boot.

```bash
php vendor/bin/drush pm:uninstall quickedit color rdf ckeditor
php vendor/bin/drush config:set system.theme default olivero
php vendor/bin/drush pm:uninstall classy stable seven
```

Modules removed from D10 core:
- `quickedit` — removed entirely
- `color` — removed entirely
- `rdf` — removed entirely
- `ckeditor` — replaced by `ckeditor5` in D10 core
- `classy` theme — removed from core, now a contrib module
- `stable` theme — removed from core, now a contrib module
- `seven` theme — removed from core, replaced by `claro`

### Step 3: Update composer.json — core and tooling constraints

Change in `composer.json`:
```json
"drupal/core-composer-scaffold": "^10",
"drupal/core-project-message": "^10",
"drupal/core-recommended": "^10",
"drush/drush": "^12",
"composer/installers": "^2"
```

Key contrib version bumps also required:
```bash
composer require drupal/easy_breadcrumb:^2.0
composer require drupal/views_bulk_operations:^4.0
```

### Step 4: Remove packages that blocked D10 composer resolution

These had to be fully removed from `composer.json` before `composer update`
would resolve:

```bash
# Abandoned, no D10 support
composer remove drupal/console drupal/console-extend-plugin

# PhantomJS tools — required old Guzzle ~5|~6, incompatible with Guzzle 7
composer remove jcalderonzumba/gastonjs jcalderonzumba/mink-phantomjs-driver

# Entire require-dev removed: behat/mink-goutte-driver (abandoned) blocked PHP 8.4
composer remove --dev behat/mink behat/mink-goutte-driver mikey179/vfsstream

# Conflicted with drupal/core-recommended's pinned Symfony version
composer remove symfony/var-dumper
```

### Step 5: Remove obsolete patches from composer.json

These patches targeted modules that no longer exist in D10:
- `"Fix summary label being duplicated during quickedit"` — quickedit gone
- `"Fix quickedit null check definitions"` — quickedit gone
- `"Fix autocomplete jquery ui dep in D9.2"` (search_autocomplete) — no longer needed

### Step 6: Add packages required for D10

```bash
# D10 compat layer for CKEditor — required for two reasons:
#   1. CKEditor 4→5 migration has not yet been run
#   2. geolocation_update_8301 update hook references the ckeditor module;
#      without it installed, drush updb fails
composer require drupal/ckeditor:^1.0

# gary theme uses classy as base theme; classy was removed from D10 core
# Note: classy ^2.0 requires stable ^2.1 which requires drupal/core ^10.3+
# This is why we targeted D10.6, not D10.0
composer require drupal/classy:^2.0
```

### Step 7: Run composer update

```bash
composer update drupal/core-* --with-all-dependencies --ignore-platform-req=ext-zip
```

The `--ignore-platform-req=ext-zip` flag was needed because `ext-zip` was not
present for the version of PHP being resolved against at that point in the process.

### Step 8: Update all custom module .info.yml files

Every custom module under `web/modules/custom/` had:
```yaml
core_version_requirement: ^8.8.0 || ^9.0
```

Updated all to:
```yaml
core_version_requirement: ^9.0 || ^10.0
```

Find affected files:
```bash
grep -rl "core_version_requirement" web/modules/custom/
```

### Step 9: Run database updates

```bash
php vendor/bin/drush updb
```

**Known issues encountered during updb:**

- **`geolocation_update_8301`** — failed if `drupal/ckeditor` was not installed
  first. Fix: install `drupal/ckeditor ^1.0`, then re-run `drush updb`.
- **VBO schema warning (8034→8035)** — this was a warning only, not a failure.
  Updates completed successfully at schema 8036.

```bash
php vendor/bin/drush cr
```

### Step 10: Config sync

```bash
php vendor/bin/drush config:import
php vendor/bin/drush cr
```

---

## Phase 3: MySQL 5.7 (Docker) → MySQL 8 (local)

MySQL 5.7 was running in Docker on port 3307. A local MySQL 8 instance was
already available.

### Dump from Docker MySQL 5.7

Using the MySQL 8 client to dump from MySQL 5.7 requires `--column-statistics=0`
— without it, mysqldump 8.x tries to query `information_schema.COLUMN_STATISTICS`
which doesn't exist in MySQL 5.7 and will error.

```bash
mysqldump \
  -h 127.0.0.1 -P 3307 -u root -proot \
  --column-statistics=0 \
  --single-transaction \
  --set-gtid-purged=OFF \
  --routines \
  --triggers \
  --no-tablespaces \
  --hex-blob \
  gary > gary_mysql57_dump_YYYYMMDD.sql
```

The dump produced was ~327MB. Verified it was free of MySQL 8 incompatibilities
(`NO_AUTO_CREATE_USER`, `utf8mb3`, `ROW_FORMAT=FIXED` — all absent).

### Create database and user on MySQL 8

```sql
CREATE DATABASE gary CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dbuser'@'localhost' IDENTIFIED BY 'dbpass123!';
GRANT ALL PRIVILEGES ON gary.* TO 'dbuser'@'localhost';
FLUSH PRIVILEGES;
```

### Import dump into MySQL 8

```bash
mysql -h 127.0.0.1 -u dbuser -p'dbpass123!' gary < gary_mysql57_dump_YYYYMMDD.sql
```

### Update Drupal settings.php

Updated `web/sites/default/settings.php` database array:
```php
$databases['default']['default'] = array (
  'database' => 'gary',
  'username' => 'dbuser',
  'password' => 'dbpass123!',
  'prefix' => '',
  'host' => '127.0.0.1',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
```

Verified connection with:
```bash
php vendor/bin/drush status
# Confirmed: Database: Connected, DB port: 3306
```

---

## Post-Upgrade Code Fixes

### Fix: Symfony EventSubscriber type hint (D10 / Symfony 6)

`GetResponseEvent` was renamed to `RequestEvent` in Symfony 5. Drupal 10 uses
Symfony 6, so any custom EventSubscribers using the old class will fatal on boot.

**File:** `web/modules/custom/gary_custom/src/EventSubscriber/CustomEventSubscriber.php`

Change:
```php
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
// ...
public function checkAuthStatus(GetResponseEvent $event) {
```

To:
```php
use Symfony\Component\HttpKernel\Event\RequestEvent;
// ...
public function checkAuthStatus(RequestEvent $event) {
```

Then clear cache:
```bash
php vendor/bin/drush cr
```

### Nginx config updates

See `.nginx.conf.sample` in project root for the full corrected config.

Key changes from the old config:
- Added explicit `listen 80;`
- Moved `fastcgi_param PHP_VALUE` from `location /` (where it was ignored) into
  the `location ~ '\.php$'` fastcgi block where it actually applies
- Replaced `try_files $uri @rewrite` (old Drupal 6/7 `?q=` format) with
  `try_files $uri /index.php?$query_string` (D8+ format) in image styles and
  static asset locations
- Removed the obsolete `location @rewrite` block

PHP-FPM: `fastcgi_pass 127.0.0.1:9082` is correct — this is the PHP 8.4 pool
(`/etc/php/8.4/fpm/pool.d/gateway.9082.conf`).

---

## Still To Do (before Drupal 11)

1. **Run CKEditor 4 → 5 migration**
   - The `drupal/ckeditor ^1.0` compat package can be removed after this is confirmed working
   - Run via the Drupal UI: Admin → Reports → CKEditor 5 migration

2. **Upgrade to Drupal 11**
   - Requirements: Drush 13, PHP 8.3+, MySQL 8+ (already done)
   - Update `composer.json` core constraints to `^11`
   - Update `drush/drush` to `^13`
   - Run `composer update drupal/core-* --with-all-dependencies`
   - Run `php vendor/bin/drush updb && php vendor/bin/drush cr`
   - Note: JSON:API dropped some DB upgrade hooks in D11 — always run `drush updb` at every step
