#/usr/bin/env bash
echo 'If you would like to execute this script, comment the exit statement below this message'
exit 1
composer.phar install
bin/console doctrine:database:drop --force
bin/console doctrine:database:create
bin/console doctrine:schema:update --dump-sql
bin/console doctrine:schema:update --force
php -d memory_limit=2G bin/console app:import:city-state-county
bin/console doctrine:fixtures:load --append
bin/console doctrine:fixtures:load --group=CMSJobCategoriesFixture --append
bin/console app:generate:job-title:models
bin/console doctrine:migrations:version --add --all
bin/console ckeditor:install public/bundles/fosckeditor --release=basic
bin/console assets:install
yarn install
yarn encore dev
php -d memory_limit=2G bin/console cache:clear
bin/console fos:user:create webmaster webmaster@endertech.com Kroq1067! --super-admin
