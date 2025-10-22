#!/bin/bash
# Execute PHP commands as www-data user
exec su www-data -s /bin/bash -c "cd $(pwd) && php artisan \"\$*\""
