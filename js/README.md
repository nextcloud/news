# News

You'll need node.js version > 0.8

## Compile coffeescript
To compile the coffeescript run:

    make
    
If you want to autocompile on change run:

    make watch

## Running unittests
Unittests are run with the testacular:

	make testacular

afterwards the watch command can be run in a new terminal:

	make watch

This will automatically execute unittests when a coffeescript file has been changed and saved.

### PHPUnit
To run phpunittests once a file changed, simply run

    make phpunit

## Clear compiled folder
To clear the build/ folder run:

    make clean

## Run js unittests
To run js unittests with the ci server, use 

	make test