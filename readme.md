# Downloading songs from songs.pk using songspk-dl written in php.

Hey guys!!! How much time does it take for you to download a 100 songs/albums from a site like songs.pk?? I have something that will just take a second of yours to download 100 songs in one go. Isn't that awesome!!! 

### Getting Started
These instructions will allow you to download songs in bulk from songs.pk on your local machine in just few steps. 

### Installing

A step by step series that will help you to install the package:

#### Step 1 : Download Composer (Ignore if you have already installed composer)
[https://getcomposer.org/download/](https://getcomposer.org/download/)


#### Step 2 : Make the folder where you want to store the songs.
    mkdir ~/Desktop/songs
    cd ~/Desktop/songs
or you can right click and create a new folder.
#### Step 3 : Type the following command to install the package.
    composer require md-adil/songspk-dl dev-master

#### Step 4 :  Following command will get all the songs from https://songspk.mobi/.  
    ./vendor/bin/songspk-dl [argument] [--page]
    or in Windows
    vendor\bin\songspk-dl [argument] [--page]

### Here are few argument you can use :
#### argument (optional)
Download latest bollywood in the provided slug only first

    browse/bollywood-albums

Download latest ghazals.

    browse/ghazals

#### page (optional)

The –-page option can be used in multiple ways.

To get all downloads from page 1 

    --page=1
Range to download from page 1 to 5.

    --page=1-5

To download only page 1 and 3.

    --page=1,3

### You can also install globally.
    composer global require md-adil/songspk-dl

( Make sure you added in path ) [https://getcomposer.org/doc/03-cli.md](https://getcomposer.org/doc/03-cli.md#global)
then just run `songspk-dl` from any directory where you wish to download.

### Examples
To Donwload all albums from songspk home page

    ./vendor/bin/songspk-dl

To Download songs from provided url, visit the site to get required url/slug.

    ./vendor/bin/songspk-dl browse/bollywood-albums 

To Download songs from provided url only for page 1.

    ./vendor/bin/songspk-dl browse/bollywood-albums --page=1

To Download songs from provided url pages 1 to 10.

    ./vendor/bin/songspk-dl browse/bollywood-albums --page=1-10

##### Note:
    Windows users can skip `.` and replace `/` with `\`
    
Please, Dont forget to give stars on [github.com/md-adil/songspk-dl](https://github.com/md-adil/songspk-dl).

### Bye :)