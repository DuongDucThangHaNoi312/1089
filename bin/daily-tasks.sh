#/usr/bin/env bash
cd "$(dirname "$0")"
cd ..
bin/console app:subscription:update:free
bin/console app:subscription:city-user-daily-check
bin/console app:subscription:job-seeker-daily-check
bin/console app:subscription-plan:update-pricing
bin/console app:generate:job-title:models
bin/console app:job-seeker:notify
bin/console app:job-seeker-user:login-frequency
yarn encore dev
yarn encore production