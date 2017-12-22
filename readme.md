# Downloading songs from songs.pk using songspk-dl written in php.

Hey guys!!!
How much time does it take for you to download a 100 songs from a site like songs.pk??
I have something that will just take a second of yours to download 100 songs in one go. Isn't that awesome!!!
Please follow these 4 quick & easy steps:

Step 1. Download Composer if you don't have already. https://getcomposer.org/download/

Step: 2. `composer require md-adil/songspk-dl`
Step: 3. `cd songspk-dl`
Step: 4. `./vendor/bin/songspk-dl [argument] [--page]` 
    or in Windows `vendor\bin\songspk-dl [argument] [--page]` 


here are few argument you can use

### argument (optional)
    `browse/bollywood-albums` Download latest bollywood in the provided slug only first
    `browse/ghazals` Download latest ghazals.

### page (optional)
    `--page=1` Get all songs from page 1.
    `--page=1-4` Get all songs from page 1 to 4.
    `--page=1,3` Get songs from page 1 and 3. 


## you can also install globally.

`composer global require md-adil/songspk-dl` ( Make sure you added in path ) https://getcomposer.org/doc/03-cli.md#global

then just run `songspk-dl` in any directory


