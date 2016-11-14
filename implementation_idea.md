Implementation Idea
===================


## Loading a crushed file

Once files have been crushed you will tell the CNC system you want to load the file or files:

`CacheNCrunch::load("testJs", "/static/testJs.js", __DIR__ . "/../../../static/testJs.js");`

or

```
CacheNCrunch::load("testA", "/static/testA.js", __DIR__ . "/../../../static/testA.js");
CacheNCrunch::load("testB", "/static/testB.js", __DIR__ . "/../../../static/testB.js");
CacheNCrunch::load("testC", "/static/testC.js", __DIR__ . "/../../../static/testC.js");
```

Like crushing, you give it a unique name, web path and file path.

Once this is done you can ask to print out the script imports.

When crushing multiple files the unique filenames are hashed together to create a unique hash string for their combination.

pseudocode:
```
Order unique names in specific order
md5 each name
contatanate each md5
md5 final string
```

This will produce a single MD5 which will be used to store details of the specific file.

### Production mode

In production mode the php file is loaded. If your file isnt present then it will throw an exception.

Once the imports are requested this will load up the single file that is the result of the crunching.

### Development mode

In development mode the json file is loaded. If the file hasnt been crunched in prod or dev mode it will crunch it.
Once it has been crunched it will be stored in dev mode and still will not be used in prod mode.
The original prod mode file, if it exists, will be kept.

If this is too slow then it will just store details of the file and not crunch it.

After crunching it will reference each file directly rather than loading the dev mode file.