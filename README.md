Setup Notes:

Install XAMPP and start Apache and MySQL.
Create a database named reservenu_db.
Import the table structure. I saw no .sql file in the zip, so export one from phpMyAdmin (Export, then SQL) and commit it, or nobody can recreate your tables. The repo needs this most.
Run composer install.
Add your own Google credentials.json to admin/php/.
If you ever host it, change the password on the server only. Don’t commit that version.
