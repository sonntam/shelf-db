/*

https://stackoverflow.com/questions/9083464/i18n-localization-plugin-for-jquery-mobile

TO use it, just "mark" an html element

<h1 uilang="unknown_user"></h1>
or call

Lang.get('unknown_user')
to get the localized string

To initialize, call the "constructor"

new Lang("ITA", true);
To use it specifyng a language,

<script type="text/javascript" src="js/Lang.js?language=ita"></script>
 */

var Lang = (function(){
  /**
  * Thanks to: http://wowmotty.blogspot.com/2010/04/get-parameters-from-your-script-tag.html
  * @param n
  * @param s
  */
  var _gup = function(n,s){
    n = n.replace(/[\[]/,"\\[").replace(/[\]]/,"\\]");
    var p = (new RegExp("[\\?&]"+n+"=([^&#]*)")).exec(s);
    return (p===null) ? "" : p[1];
  };

  // Grab the parameters from my url, and initialize myself!
  var _scriptSrc = $('script[src*=langprovider]').attr('src');
  var _lang      = _gup('language',_scriptSrc);
  var _replace   = _gup('replace',_scriptSrc).toLowerCase() === "true";

  /**
  *
  * @param language The language to use
  * @param replaceText If true, replace all the occurency marked with placemark {lang=<key>}
  */
  var _Lang = function(language, replaceText)
  {

    // GENERAL SETTINGS

    var LANG_CURRENT = language;

    var LANG_DEFAULT = 'enUS';

    /**
    * All the html elements with this attributes are translated on the fly
    */
    var LANG_ATTRIBUTE_NAME = "uilang";

    var _Languages = {
      enUS: {
        ok: 'Ok',
        name: 'Name',
        yes: 'Yes',
        no: 'No',
        searchPlaceholder: 'Search...',
        account: 'Account',
        logout: 'Logout',
        administration: 'Administration',
        edit: 'Edit',
        copy: 'Copy',
        move: 'Move',
        delete: 'Delete',
        compress: 'Compress',
        expand: 'Expand',
        cancel: 'Cancel',
        abort: 'Abort',
        none: 'None',
        tools: 'Tools',
        description: 'Description',
        suppliers: 'Suppliers',
        footprint: 'Footprint',
        footprints: 'Footprints',
        categories: 'Categories',
        partTitle: 'Part details',
        moreInfo: 'Learn more',
        enterName: 'Enter name',
        enterPrice: 'Enter price',
        storageLocation: 'Storage location',
        storageLocations: 'Storage locations',
        editCategories: 'Edit categories',
        componentDatabase: 'Component database',
        mainTitle: 'SHELF-DB Component Database',
        areYouSureQuestion: 'Are you sure?',
        editCategoryMove: 'Kategorie verschieben',
        editCategoryMoveConfirmTest: function(nodeName, targetNodeName) {
          return 'Move category "'+nodeName+'" as well as all its sub groups to "'+ (targetNodeName || 'root level') +'?';
        },
        editCategoryRemoveLeafHint: function(parentNodeName) {
          return 'Contained parts will be moved to the parent category "' + parentNodeName + '".';
        },
        editCategoryRemoveLeafQuestion: function( categoryName, subLeavesNamesHint, subLeavesMoveHint ) {
          return 'Do you really want to delete the category "'+categoryName+'"' + subLeavesNamesHint + '?'
            + ' ' + subLeavesMoveHint + ' This action cannot be reverted.';
        },
        editCategoryRemoveSubLeavesHint: function(subLeavesString) {
          return ' as well as the sub categories ' + subLeavesString;
        },
      },

      deDE: {
        ok: 'Ok',
        yes: 'Ja',
        no: 'Nein',
        any: 'Beliebig',
        date: 'Datum',
        add: 'Anlegen',
        show: 'Zeigen',
        new: 'Neu',
        edit: 'Bearbeiten',
        close: 'Schließen',
        name: 'Name',
        copy: 'Kopieren',
        change: 'Ändern',
        move: 'Verschieben',
        delete: 'Löschen',
        reset: 'Zurücksetzen',
        compress: 'Zuklappen',
        expand: 'Aufklappen',
        searchPlaceholder: 'Suche...',
        cancel: 'Abbrechen',
        parts: 'Teile',
        abort: 'Abbrechen',
        none: 'Keine',
        noSearchResults: 'Keine Ergebnisse...',
        tools: 'Werkzeuge',
        settings: 'Einstellungen',
        image: 'Bild',
        price: 'Preis',
        images: 'Bilder',
        masterImage: 'Hauptbild',
        input: 'Eingabe',
        copyOf: function(name) {
          return 'Kopie von '+name;
        },
        account: 'Benutzerkonto',
        login: 'Einloggen',
        logout: 'Ausloggen',
        logoutUser: function(username) {
          return username + " ausloggen";
        },
        username: 'Benutzername',
        password: 'Passwort',
        forgotPasswordLink: 'Passwort vergessen? »',
        register: 'Registrieren',
        administration: 'Administration',
        uploadImageLabel: 'Bild hochladen',
        upload: 'Hochladen',
        description: 'Beschreibung',
        datasheets: 'Datenblätter',
        supplier: 'Lieferant',
        history: 'Verlauf',
        actions: 'Aktionen',
        suppliers: 'Lieferanten',
        footprints: 'Bauformen',
        footprint: 'Bauform',
        category: 'Kategorie',
        categories: 'Kategorien',
        categoryNameHeader: function(catname) {
          return 'Kategorie ' + catname;
        },
        partsInCategoryNameHeader: function(catname) {
          return 'Teile in Kategorie ' + catname;
        },
        subCategories: 'Unterkategorien',
        parentCategories: 'Übergeordnete Kategorien',
        partTitle: 'Bauteilansicht',
        partNumber: 'Artikelnummer',
        amountStored: 'Menge eingelagert',
        amountAvailable: 'Menge vorhanden',
        amountLeast: 'Mindestmenge',
        moreInfo: 'Mehr informationen',
        inStockColumnHeader: 'Lager',
        totalStockColumnHeader: 'Vorh.',
        minStockColumnHeader: 'Min.',
        noUndoHint: 'Diese Aktion kann nicht rückgängig gemacht werden.',
        loginHint: 'Geben Sie Benutzername und Passwort ein, um sich einzuloggen.',
        searchResults: 'Suchergebnisse',
        hideSubcategories: 'Unterkategorien ausblenden',
        noGroupingByCategories: 'Nicht gruppieren',
        searchResultsFor: function(str) {
          return 'Suchergebnisse für "'+$.trim(str)+'"';
        },
        upperLevel: 'Ebene höher',
        enterName: 'Namen eingeben',
        enterPrice: 'Preis eingeben',
        enterAmount: 'Menge eingeben',
        enterUsername: 'Benutzername eingeben',
        enterPassword: 'Passwort eingeben',
        enterUrl: 'Geben Sie eine Adresse an',
        enterDescription: 'Beschreibung eingeben',
        enterPartNumber: 'Artikelnummer eingeben',
        resetImage: 'Bild wiederherstellen',
        defaultImage: 'Standardbild',
        storageLocation: 'Lagerort',
        storageLocations: 'Lagerorte',
        storageLocationShowNonEmpty: 'Belegte Fächer',
        storageLocationShowEmpty: 'Leerfächer',
        noFootprintShow: 'Teile ohne Bauform',
        noStorageLocationShow: 'Teile ohne Lagerort',
        noSupplierShow: 'Teile ohne Lieferant',
        icLogosShow: 'IC Hersteller',
        orderItemsShow: 'Zu bestellende Teile',
        statisticsShow: 'Statistiken',
        editCategories: 'Kategorien bearbeiten',
        editFootprints: 'Bauformen bearbeiten',
        componentDatabase: 'Bauteiledatenbank',
        mainTitle: 'SHELF-DB Bauteile-Datenbank',
        areYouSureQuestion: 'Sind Sie sicher?',
        searchTableTitle: 'Suchergebnisse in allen Bauteilen',
        searchCategoryTableTitle: function(categoryName) {
          return 'Suchergebnisse in Bauteilen der Kategorie \"'+categoryName+'\"';
        },
        editPartNewName: 'Neuer Name',
        editPartChangeName: 'Benennung ändern',
        editPartPartNumber: 'Neue Artikelnummer',
        editPartChangePartNumber: 'Artikelnummer ändern',
        editPartStock: 'Eingelagerte Menge ändern',
        editPartChangeStock: 'Geben Sie die neue eingelagerte Menge ein.',
        editPartTotal: 'Gesamtmenge ändern',
        editPartChangeTotal: 'Geben Sie die neue Gesamtmenge ein.',
        editPartPrice: 'Preis ändern',
        editPartChangePrice: 'Geben Sie einen Preis für das Teil an.',
        editPartMinStock: 'Mindestmenge ändern',
        editPartChangeMinStock: 'Geben Sie eine Mindestmenge für das Teil ein.',
        editPartDelete: 'Teil löschen',
        editPartDeletePicture: 'Teilebild löschen',
        editPartDescriptionEdit: 'Beschreibung bearbeiten',
        editPartDescriptionEditHint: 'Ändern Sie hier die bestehende Beschreibung.',
        editCategoryNewName: 'Neuer Name',
        editCategoryChange: 'Kategorie ändern',
        editCategoryAdd: 'Kategorie anlegen',
        editCategoryAddRootHint: 'Namen für neue Wurzelkategorie eingeben',
        editCategoryAddHint: function(parentNodeName) {
          return "Namen für neue Kategorie innerhalb von \""+parentNodeName+"\" eingeben";
        },
        editCategoryMove: 'Kategorie verschieben',
        editCategoryDelete: 'Kategorie löschen',
        editCategoryMoveConfirmTest: function(nodeName, targetNodeName) {
          return 'Kategorie "'+nodeName+'" sowie alle Untergruppen nach "'+ (targetNodeName || 'Wurzelebene') +'" verschieben?';
        },
        editCategoryRemoveLeafHint: function(parentNodeName) {
          return 'Enthaltene Teile werden in die übergeordnete Kategorie "'+parentNodeName+'" verschoben.';
        },
        editCategoryRemoveLeafQuestion: function( categoryName, subLeavesNamesHint, subLeavesMoveHint ) {
          return 'Möchten Sie die Kategorie "'+categoryName+'"' + subLeavesNamesHint + ' wirklich löschen?'
            + ' ' + subLeavesMoveHint + ' Diese Aktion kann nicht rückgängig gemacht werden.';
        },
        editCategoryRemoveSubLeavesHint: function(subLeavesString) {
          return ' sowie die Unterkategorien ' + subLeavesString;
        },
        editCategoriesDragDropHint: 'Drag & Drop zum Verschieben von Knoten',
        editCategoriesClickNodeHint: 'Anklicken eines Knotens zum Anzeigen weiterer Optionen',
        editCategoriesDeleteHint: 'Nur wenn eine Kategorie sowie die Unterkategorien leer sind, kann ein ganzer Zweig gelöscht werden.',
        editCategoriesDeleteMigrateHint: 'Wird eine nicht-leere Kategorie am Ende eines Baums gelöscht, werden die enthaltenen Elemente der übergeordneten Ebene zugeordnet.',
        editCategoriesRootDeleteHint: 'Kategorien auf der Wurzelebene können nur gelöscht werden, wenn sie leer sind (grau)',
        editFootprintNewName: 'Neuer Name',
        editFootprintAdd: 'Bauform anlegen',
        editFootprintDelete: 'Bauform löschen',
        editFootprintDeleteHint: function(footprintName) {
          return 'Möchten Sie die Bauform "' + footprintName + '" wirklich löschen? Bauteile, die diese Bauform nutzen, werden auf die Bauform "-" zurückgesetzt und sind in der Werkzeugleiste auflistbar.';
        },
        editFootprintAddHint: 'Namen und Bild für neue Bauform angeben',
        popupFootprintSelectHeader: 'Bauteilform wählen',
        popupFootprintEditHeader: 'Bauteilform bearbeiten',
        popupFootprintAddHeader: 'Bauteilform anlegen',
        popupFootprintSelectUserAction: 'Wählen Sie eine Form aus',
        popupFootprintAddUserAction: 'Geben Sie die Daten für die neue Bauform an',
        popupFootprintFilterHint: 'Mit dem Filter kann in den Formen gesucht werden',
        popupFootprintFilterPlaceholder: 'Einträge filtern...',

        popupUploadFile: 'Datei hochladen',
        popupUploadFileUserAction: 'Wählen Sie eine Datei zum Hochladen aus.',

        showEmptyStoreLocations: "Leerfächer anzeigen",
        showNonEmptyStoreLocations: "Belegte Fächer anzeigen",

        editStoreLocations: "Fächer bearbeiten",
        editStoreLocationNewName: 'Neuer Name',
        editStoreLocationAdd: 'Lagerort anlegen',
        editStoreLocationDelete: 'Lagerort löschen',
        editStoreLocationDeleteHint: function(storelocName) {
          return 'Möchten Sie den Lagerort "' + storelocName + '" wirklich löschen? Bauteile, die diesen Lagerort nutzen, werden auf den Lagerort "-" zurückgesetzt und sind in der Werkzeugleiste auflistbar.';
        },
        editStoreLocationAddHint: 'Namen für neuen Lagerort angeben',
        popupStoreLocationSelectHeader: 'Lagerort wählen',
        popupStoreLocationEditHeader: 'Lagerort bearbeiten',
        popupStoreLocationAddHeader: 'Lagerort anlegen',
        popupStoreLocationSelectUserAction: 'Wählen Sie einen neuen Lagerort aus',
        popupStoreLocationAddUserAction: 'Geben Sie die Daten für den neuen Lagerort an',
        popupStoreLocationEditUserAction: 'Geben Sie die neuen Daten für den Lagerort an',
        popupStoreLocationFilterHint: 'Mit dem Filter kann in den Lagerorten gesucht werden',
        popupStoreLocationFilterPlaceholder: 'Einträge filtern...',

        editSuppliers: 'Lieferanten bearbeiten',
        editSupplierNewName: 'Neuer Name',
        editSupplierAdd: 'Lieferant anlegen',
        editSupplierDelete: 'Lieferanten löschen',
        editSupplierDeleteHint: function(supplierName) {
          return 'Möchten Sie den Lieferanten "' + supplierName + '" wirklich löschen? Bauteile, die diesen Lieferanten nutzen, werden auf "-" zurückgesetzt und sind in der Werkzeugleiste auflistbar.';
        },
        editSupplierArticleUrl: 'Suchadresse',
        editSupplierArticleUrlHint: 'Die Suchadresse kann angegeben werden, um aus der Teilenummer eines Teils einen Link zu erzeugen. Dazu können Sie hier den Platzhalter <!PARTNR!> eingeben, der mit der Teilenummer ersetzt wird. Es können auch Ersetzungen angegeben werden. Um z.B. ein " " durch "-" und "!" durch "?" zu ersetzen, geben Sie an der entsprechenden Stelle in der Adresse <!PARTNR; :-;!:?!> ein.',

        editSupplierAddHint: 'Namen und Bild für neuen Lieferanten angeben',
        popupSupplierSelectHeader: 'Liefertant wählen',
        popupSupplierEditHeader: 'Lieferant bearbeiten',
        popupSupplierAddHeader: 'Lieferant anlegen',
        popupSupplierSelectUserAction: 'Wählen Sie einen Lieferanten aus',
        popupSupplierAddUserAction: 'Geben Sie die Daten für den neuen Lieferanten an',
        popupSupplierEditUserAction: 'Geben Sie die neuen Daten für den Lieferanten an',
        popupSupplierFilterHint: 'Mit dem Filter kann in den Lieferanten gesucht werden',
        popupSupplierFilterPlaceholder: 'Einträge filtern...',


        popupStorelocationHeader: 'Lagerort wählen',
        popupStorelocationUserAction: 'Wählen Sie einen Lagerort aus',
        popupStorelocationFilterHint: 'Mit dem Filter kann in den Lagerorten gesucht werden',
        popupStorelocationFilterPlaceholder: 'Lagerorte filtern...',

        helpInStock: 'Die eingelagerte Menge ist die Menge, die im Lagerort verfügbar ist. Sie ist nicht größer als die vorhandene Menge.',
        helpTotalInStock: 'Die vorhandene Menge ist die Menge, die überhaupt existiert, aber nicht unbedingt im Lagerort verfügbar ist. Sie ist immer mindestens so groß, wie die eingelagerte Menge.',

        // History related
        historyUserPartChangeTotalStock: function(user, oldCount, count) {
          var change = parseInt(count)-parseInt(oldCount);
          return user + ' hat ' + (Math.abs(change) == 1 ? 'ein' : parseInt(Math.abs(change)) ) + ' Stück ' + (change > 0 ? 'zur Gesamtmenge hinzugefügt.' : 'von der Gesamtmenge entfernt.' );
        },
        historyUserPartChangeInStock: function(user, oldCount, count) {
          var change = parseInt(count)-parseInt(oldCount);
          return user + ' hat ' + (Math.abs(change) == 1 ? 'ein' : parseInt(Math.abs(change)) ) + ' Stück ' + (change > 0 ? 'zur Lagermenge hinzugefügt.' : 'aus der Lagermenge entnommen.' );
        },
        historyUserPartChangeMinStock: function(user, oldCount, count) {
          return user + ' hat die Mindestmenge auf ' + (Math.abs(count) == 1 ? 'ein' : parseInt(Math.abs(count)) ) + ' Stück geändert.';
        },
        historyUserPartChangePartNumber: function(user, oldNumber, newNumber) {
          return user + ' hat die Artikelnummer von ' + oldNumber + ' auf ' + newNumber + ' geändert.';
        },
        historyUserPartChangeFootprint: function(user, oldVal, newVal) {
          return user + ' hat die Bauform von ' + oldVal + ' auf ' + newVal + ' geändert.';
        },
        historyUserPartChangeStorageLocation: function(user, oldVal, newVal) {
          return user + ' hat den Lagerort von ' + oldVal + ' auf ' + newVal + ' geändert.';
        },
        historyUserPartChangeSupplier: function(user, oldVal, newVal) {
          return user + ' hat den Lieferanten von ' + oldVal + ' auf ' + newVal + ' geändert.';
        },
        historyUserPartChangePrice: function(user, oldVal, newVal) {
          return user + ' hat den Preis von ' + oldVal + ' auf ' + newVal + ' geändert.';
        },
        historyUserPartChangeName: function(user, oldVal, newVal) {
          return user + ' hat den Namen von ' + oldVal + ' auf ' + newVal + ' geändert.';
        },
        historyUserPartChangeDescription: function(user) {
          return user + ' hat die Beschreibung geändert.';
        },
        historyUserPartAddPicture: function(user) {
          return user + ' hat ein Bild hinzugefügt.';
        },
        historyUserPartDeletePicture: function(user) {
          return user + ' hat ein Bild gelöscht.';
        },
        historyUserPartNewMasterPicture: function(user) {
          return user + ' hat ein neues Bild als Hauptbild ausgewählt.';
        },
        historyUserPartAddDatasheet: function(user) {
          return user + ' hat ein neues Datenblatt hinzugefügt.';
        },
        historyUserPartDeleteDatasheet: function(user) {
          return user + ' hat ein Datenblatt gelöscht.';
        },
        historyUserPartDelete: function(user, partname) {
          return user + ' hat das Teil "' + partname + '" gelöscht.';
        },
        historyUserPartAdd: function(user, partname) {
          return user + ' hat das Teil "' + partname + '" angelegt.';
        }
      }
    };

    // Verify if language exists, else return to default
    if( !_Languages.hasOwnProperty(language) ) {
      console.log("Language " + language + " is not available. Defaulting to " + LANG_DEFAULT + ".");
      LANG_CURRENT = LANG_DEFAULT;
    }

   /**
    * key è la chiave da usare nell'oggetto LANG
    * @param key
    */
    var get = function(key, forceFunction) {
      var str;
      if( typeof forceFunction === "undefined" ) {
        forceFunction = false;
      }
      // Check if translation is available
      if( _Languages[LANG_CURRENT].hasOwnProperty(key) ) {
        var lstr = _Languages[LANG_CURRENT][key] || _Languages[LANG_DEFAULT][key];
        if( lstr === undefined ) {
          console.log('Lang: Key "'+key+'" could not be found!');
          if( forceFunction ) str = function() { return key; };
          else str = key;
        } else {
          str = lstr;
        }
      } else {
        console.log('Lang: Key "'+key+'" could not be found!');
        if( forceFunction ) str = function() { return key; };
        else str = key;
      }
      return str;
    };

   /**
    * Searches for the LANG_ATTRIBUTE_NAME attribute and uses it for
    * text replacement. There are several valid forms. Suppose the attribute name
    * is 'uilang':
    *
    * 1. uilang="langString" - This replaces the elements inner text with the
    *    string associated with "langString" in the localization table
    * 2. uilang="otherAttr:langString" - This sets the element's attribute
    *    "otherAttr" to the
    *    string associated with "langString" in the localization table
    * 3. uilang=":langFuntion"  - This sets the element's text to the return value
    *    of the function 'langFunction' from the localization table. The inner
    *    text of the element is passed as argument to that function.
    * 4. uilang="otherAttr:langFunction" - Similar to the above but now the
    *    element's attribute 'otherAttr' is set to the return value of the
    *    function 'langFunction'
    * 5. uilang="otherAttr:langFunction:yetAnotherAttr" - This sets the element's
    *    attribute "otherAttr" to the return value of the function 'langFunction'
    *    from the localization table where the text within the element's attribute
    *    'yetAnotherAttr' is passed as argument to that function.
    * 6. uilang="spec1;spec2;..." is semi-colons to pass multiple of the
    *    specifications from above to the localization engine.
    */
    var searchAndReplace = function() {
      var divs = $('*[' + LANG_ATTRIBUTE_NAME + ']');

      $.each(divs,function(indx,item)
      {
        item = $(item);

        var att = item.attr(LANG_ATTRIBUTE_NAME);
        att.split(";").forEach( function(langSet) {
          var attr = langSet.split(":");

          var setAttr = null;
          var strName = null;
          var strArg  = null;

          if( attr.length == 1 ) { // Simplereplace
            strName = attr[0];
          } else if( attr.length == 2 ) { // Set attribute
            setAttr = attr[0];
            strName = attr[1];
          } else if( attr.length >= 3 ) { // Function call
            setAttr = attr[0];
            strName = attr[1];
            strArg  = attr.slice(2);
          }

          var txt = get(strName);

          // If the string is of function type, try to apply the inner Html as argument
          if( typeof txt === 'function' ) {
            if( strArg && strArg.some(function(e){ return (e?true:false); }) ) {
              strArg = strArg.map( function(el) {
                return item.attr(el);
              });

              txt = txt.apply(this,strArg);
            } else {
              txt = txt(item.text());
            }
          }

          if( setAttr && setAttr != "" ) {
            item.attr(setAttr,txt);
          }
          else
            item.text(txt);
        } );
      });

      divs.removeAttr(LANG_ATTRIBUTE_NAME);
    };

   var setLanguage = function(language, replaceText) {
       LANG_CURRENT = language;
       if(replaceText){
           searchAndReplace();
       }
   };

    // Actions

    // Attach to events for automatic language search and replace
    if(replaceText) {
      $( document ).ready( function() {
        console.log("DEBUG-LANGPROVIDER: document - ready - applying language...");
        searchAndReplace();
      });

      $(document).on('show.bs.modal', function() {
        console.log("DEBUG-LANGPROVIDER: popup - create - applying language...");
        Lang.searchAndReplace();
      });

      $(document).on("pageafterload", function(evt) {
        console.log("DEBUG-LANGPROVIDER: page - pageafterload - applying language...");
        Lang.searchAndReplace();
      });
    }


    // Returns a localized instance of language
    return {
      get: get,
      searchAndReplace: searchAndReplace,
      setLanguage: setLanguage
    };
  };

  return _Lang(_lang, _replace);
})();
