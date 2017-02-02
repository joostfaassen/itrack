iTrack
======

Track the location of your Apple devices through Apple's Find My iPhone service

This app tries to locate the specified devices every x seconds
based on provided icloud credentials.

## Usage

### Configure

Copy the `config.yml` template, and edit it based on your settings

    cp config.yml.dist config.yml
    
Specifically, edit the username/password keys to match your iCloud account.
Additionally, update the `pdo` key to match your database credentials

### Setup your database schema

    ./bin/itrack schema
    
### Find your apple device ids:

    ./bin/itrack ls
    
Now update your `config.yml` to include the apple device id's that you'd like to track

### Track your devices

    ./bin/itrack run
    
itrack will try to locate the specified apple devices every x seconds and
register their location in your database.

    
