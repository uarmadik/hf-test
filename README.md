* clone this project
* run command `composer-update` 
* configure `DATABASE_URL` in `.env` file
* run command `php bin/console doctrine:database:create`
* run command `php bin/console make:migration`
* run command `php bin/console doctrine:migrations:migrate`

run command `php bin/console app:parse-data` to parse data from API