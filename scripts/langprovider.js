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

// AutoStar!
// Grab the parameters from my url, and initialize myself! FUGOOOOO
(function __lang_init_wrapper()
{
  var scriptSrc = $('script[src*=langprovider]').attr('src');
  var lang      = gup('language',scriptSrc);

  new Lang(lang, false);

})();

/**
* Thanks to: http://wowmotty.blogspot.com/2010/04/get-parameters-from-your-script-tag.html
* @param n
* @param s
*/
function gup(n,s){
n = n.replace(/[\[]/,"\\[").replace(/[\]]/,"\\]");
var p = (new RegExp("[\\?&]"+n+"=([^&#]*)")).exec(s);
return (p===null) ? "" : p[1];
}

/**
*
* @param language The language to use
* @param replaceText If true, replace all the occurency marked with placemark {lang=<key>}
*/
function Lang(language, replaceText)
{

  var Languages =
  {
    enUS:
    {
      ok: 'Ok',
      name: 'Name',
      yes: 'Yes',
      no: 'No',
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
      storageLocation: 'Storage location',
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

    deDE:
    {
      yes: 'Ja',
      no: 'Nein',
      add: 'Anlegen',
      edit: 'Bearbeiten',
      copy: 'Kopieren',
      change: 'Ändern',
      move: 'Verschieben',
      delete: 'Löschen',
      reset: 'Zurücksetzen',
      compress: 'Zuklappen',
      expand: 'Aufklappen',
      searchPlaceholder: 'Suche...',
      cancel: 'Abbrechen',
      abort: 'Abbrechen',
      none: 'Keine',
      tools: 'Werkzeuge',
      image: 'Bild',
      copyOf: function(name) {
        return 'Kopie von '+name;
      },
      uploadImageLabel: 'Bild hochladen',
      description: 'Beschreibung',
      datasheets: 'Datenblätter',
      suppliers: 'Lieferanten',
      footprints: 'Bauformen',
      footprint: 'Bauform',
      categories: 'Kategorien',
      partTitle: 'Bauteilansicht',
      moreInfo: 'Mehr informationen',
      noUndoHint: 'Diese Aktion kann nicht rückgängig gemacht werden.',
      enterName: 'Namen eingeben',
      enterUrl: 'Geben Sie eine Adresse an',
      enterDescription: 'Beschreibung eingeben',
      resetImage: 'Bild wiederherstellen',
      defaultImage: 'Standardbild',
      storageLocation: 'Lagerort',
      storageLocationShowNonEmpty: 'Leerfächer',
      storageLocationShowEmpty: 'Belegte Fächer',
      noFootprintShow: 'Teile ohne Bauform',
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
      editPartDelete: 'Teil löschen',
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
      editCategoriesDeleteHint: 'Nur wenn eine Kategorie sowie die Unterkategorien keer sind, kann ein ganzer Zweig gelöscht werden.',
      editCategoriesDeleteMigrateHint: 'Wird eine nicht-leere Kategorie am Ende eines Baums gelöscht, werden die enthaltenen Elemente übergeordneten Ebene zugeordnet.',
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
      popupSupplierFilterHint: 'Mit dem Filter kann in den Lieferanten gesucht werden',
      popupSupplierFilterPlaceholder: 'Einträge filtern...',


      popupStorelocationHeader: 'Lagerort wählen',
      popupStorelocationUserAction: 'Wählen Sie einen Lagerort aus',
      popupStorelocationFilterHint: 'Mit dem Filter kann in den Lagerorten gesucht werden',
      popupStorelocationFilterPlaceholder: 'Lagerorte filtern...',

      helpInStock: 'Die eingelagerte Menge ist die Menge, die im Lagerort verfügbar ist. Sie ist nicht größer als die vorhandene Menge.',
      helpTotalInStock: 'Die vorhandene Menge ist die Menge, die überhaupt existiert, aber nicht unbedingt im Lagerort verfügbar ist. Sie ist immer mindestens so groß, wie die eingelagerte Menge.'
    }
  }

 // GENERAL SETTINGS

 var LANG_CURRENT = language;

 var LANG_DEFAULT = 'enUS';

 /**
  * All the html elements with this attributes are translated on the fly
  */
 var LANG_ATTRIBUTE_NAME = "uilang"


 /**
  * key è la chiave da usare nell'oggetto LANG
  * @param key
  */
this.get = function(key)
{
  var str = Languages[LANG_CURRENT][key] || Languages[LANG_DEFAULT][key];
  if( str === undefined ) {
    console.log('Lang: Key "'+key+'" could not be found!');
  }
  return str;
}

 /**
  * Cerco tutti gli elementi che hanno una certa classe
  */
this.searchAndReplace = function()
{
  var me = this;
  var divs = $('*[' + LANG_ATTRIBUTE_NAME + ']');

  $.each(divs,function(indx,item)
  {
    item = $(item);

    var att = item.attr(LANG_ATTRIBUTE_NAME);
    att.split(";").forEach( function(langSet) {
      var attr = langSet.split(":");

      var txt = me.get(attr[1] || attr[0]);

      // If the string is of function type, try to apply the inner Html as argument
      if( typeof txt === 'function' ) {
        if( attr.length > 2 ) {
          txt = txt(item.attr(attr[2]));
        } else {
          txt = txt(item.text());
        }
      }

      if( attr.length > 1 ) {
        item.attr(attr[0],txt);
      }
      else
        item.text(txt);
    } );
  });

  divs.removeAttr(LANG_ATTRIBUTE_NAME);
}

 this.setLanguage = function(language, replaceText)
 {
     LANG_CURRENT = language;
     if(replaceText){
         this.searchAndReplace();
     }
 }

 if(replaceText){
     this.searchAndReplace();
 }

 // Returns a localized instance of language
 Lang = {
     get: this.get,
     searchAndReplace: this.searchAndReplace,
     setLanguage: this.setLanguage
 };
}
