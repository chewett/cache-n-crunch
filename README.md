cache-n-crush
=============

Version 0.1 was a proof of concept to test if it worked.
Since this does indeed appear to work im going to rework it to fill a number of potential objectives:

* Ability to state which files you want to crunch and load 
* One file will be produced per set of crunch files. If you crunch A, B, and A+B they will exist as 3 separate crunch files
* The output of crunch A and B will not be used for A and B, and will be completely separate
* If the combined crunch is not available on the filesystem an exception will be thrown
* In debug mode the raw files will be loaded instead of the crunched file
* In debug mode, every time the raw files are loaded they are crunched
    * These crunched files will be saved as a new revision and will allow easy deployment calling the specific crunch method
    * When crunching files manually it will check for these new cached files and use them if present to speed up cache deployment
    * Some way to know which crunched files have new revisions you could deploy
* CSS files will be supported to merge css into a single file
* Uglify css will need to be ported over similarly to uglify js
    * maybe add some kind of similar interface, etc?
    * maybe allow any kind of crunching software to be plugged in?
* Store a list of previously crunched files, and their current file somewhere "nice"
    * Allow a user to recrunch everything if its changed using above store
    * Allow marking a crunch as "stale" after a file has been removed from the filesystem
    * Some way to view this potentially?
* Have some basic versioning of crunched files

Possible implementation
-----------------------

* JSON store to store all the data about the files
* Then export this to a basic php array file used for the main cache
* JSON file stores all the details such as new files, etc
* Each time the cache is modified json file is written out as a php file that works as the bootstrap

TODO:
-----

* Remove all comments but allow custom comments at the top of the file configurable
* Allow getting the details of all previously crushed files and their constitutent paths
* Allow crushing by identifier (using the details of the constituent parts)
* Allow checking of identifiers to find what hasnt yet been updated (has changes in constituent files)
* Comment all code
* Comment all tests
* Write some more tests
* Create an example implementation repo?
* Run speedtests on importing a json file and including the php file?
(only used the PHP file including as I have made the 
assumptions that it will be faster, possibly not true?)


Usage
-----

No documentation available currently

Installation
------------

No documentation available currently

Tests
-----

No documentation available currently

License
-------

This is licensed under the MIT license. For more information see the [LICENSE](LICENSE) file.
