Shelf-DB
========

WIP Notice
----------

This project is still in an early development stage and *not yet ready for production environments*. Because of that there are no setup instructions present yet. The code quality needs improvement, i.e. a more modular approach will be incorporated via code-refactoring in the near future.

Introduction
------------

Shelf-DB is a web application based on php/MySQL/Javascript to keep track of things. The design is mobile-first.

It is formally based on [Part-DB](https://github.com/sandboxgangster/Part-DB) but now completely rewritten from scratch, offering new features and a different UI approach.

Server Requirements
-------------------

 - php >= 7.0
   php extensions: mysqli, gd2 (for QR codes), mbstring
 - MySQL >= 5.0

Installation
------------

 1. Extract the repository contents into any folder of a webserver meeting the requirements above.
 2. Go into the `./config` folder and edit the `config.json` to meet your desired sql server settings. If you don't want to use minified file versions add `"useMinified": false` to the config file.
 3. Resolve the npm dependencies by running `npm install` within the project folder.
 5. Build the SASS files and minified file versions using `npx grunt all`. By default, the minified and compiled files are copied to the `build` directory.
 6. Launch the `index.php` main page in a web browser. A standard database structure will be created where the database name given in the config file is used.
 7. Ready for use!

Used libraries
--------------

 - [jqTree](http://mbraak.github.io/jqTree/) - Apache License 2.0
 - [jQuery](https://jquery.org) - MIT License
 - [font awesome](http://http://fontawesome.io) - SIL Open Font License / MIT License
 - [jquery-validation](https://github.com/jquery-validation/jquery-validation) - MIT License
 - [jQueryFileUpload](https://github.com/Abban/jQueryFileUpload) - MIT License
 - [qrcode-generator](https://github.com/kazuhikoarase/qrcode-generator) - MIT License
 - [free-jqgrid/jqGrid](https://github.com/free-jqgrid/jqGrid) - MIT/GPL License
 - [NameSpaceFinder php class](https://stackoverflow.com/a/22762333) - Creative Commons BY-SA 2.5 License (Share & Adapt)
 - [BlueM/Tree](https://github.com/BlueM/Tree) - BSD License
 - [twigphp/Twig](https://github.com/twigphp/Twig) - BSD License 3-clause
 - [TiGR/twig-preprocessor](https://github.com/TiGR/twig-preprocessor/tree/master/lib) - MIT License
 - [Bootstrap](http://getbootstrap.com/) - MIT License
 - [FezVrasta/popper.js](https://github.com/FezVrasta/popper.js) - MIT License
 - [stefanocudini/bootstrap-list-filter](https://github.com/stefanocudini/bootstrap-list-filter) - MIT License

 Special thanks to all the people that contributed to these projects.

Development
-----------

To update SASS styles:
 - Edit the `scss/bootstrap-custom.scss` file
 - Run `grunt scss`
 - The compiled file is placed in `styles\bootstrap-custom.scss`

TODOs
-----

 - Remove json pretty print
 - Remove debugger statements from javascript
 - Remove debug output
 - new categories must be greyed out in the edit tree view
 - decode html special chars in database
 - inline edit
 - part table search
 - part table filter
 - delete part from detail page
 - copy part from detail page
 - add datasheets and images on detail page
 - allow adding of part from part table
 - show price information in part detail view including tooltip with timestamp of last update
 - REST API
 - Refactor all the JavaScript code
 - Fix file upload. Show preview but only upload to server after user clicks "upload". Also save original filename and size of file
