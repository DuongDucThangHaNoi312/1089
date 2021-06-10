#/usr/bin/env bash
cd "$(dirname "$0")"
cd ..
bin/console app:city-user:send-monthly-report

