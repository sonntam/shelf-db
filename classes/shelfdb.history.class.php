<?php

namespace ShelfDB {
  class History {

    private $db = null;

    /** Constructor */
    function __construct($dbobj)
    {
      $this->db = $dbobj;
    }

    private function db() : \ShelfDB {
      return $this->db;
    }

    public function Add(int $itemId, $itemType, $action, $field, $oldValue, $newValue) {
      // $itemType can be one of G, U, P, SU, F, SL, PIC
      // Group, User, Part, Supplier, Footprint, Storelocation, Picture

      $userId = $this->db()->Users()->GetLoggedInUserId();
      if( !$userId ) $userId = 0; // No user is logged in

      $action   = $this->db()->sql->real_escape_string($action);
      $field    = $this->db()->sql->real_escape_string($field);
      $oldValue = ( is_string($oldValue) ?
          $this->db()->sql->real_escape_string($oldValue)
        : $this->db()->sql->real_escape_string(json_encode($oldValue))
      );
      $newValue = ( is_string($newValue) ?
          $this->db()->sql->real_escape_string($newValue)
        : $this->db()->sql->real_escape_string(json_encode($newValue))
      );
      $itemType = $this->db()->sql->real_escape_string($itemType);

      $query = "INSERT INTO history (userid, action, timestamp, itemid, itemtype, field, newvalue, oldvalue) "
        ."VALUES ($userId, '$action',NOW(), $itemId, '$itemType', '$field', '".$newValue."', '".$oldValue."');";

      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);

      return $res;
    }

    public function GetByTypeAndId(int $elementId, $elementType, int $maxCount = 10) {
      $elementType   = $this->db()->sql->real_escape_string($elementType);

      switch( $elementType ) {
        case 'P':
          $query = "SELECT u.name AS username, h.*, p.* FROM history h"
            ." LEFT JOIN parts p ON p.id = h.itemid"
            ." LEFT JOIN users u ON h.userid = u.id"
            ." WHERE h.itemtype = '$elementType' AND h.itemid = $elementId"
            ." ORDER BY h.id DESC LIMIT $maxCount;";
          break;
        default:
          return;
      }

      //$query = "SELECT u.name AS username, h.* FROM history h LEFT JOIN users u ON h.userid = u.id WHERE h.itemtype = '$elementType' AND h.itemid = $elementId ORDER BY h.id DESC LIMIT $maxCount;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
      if( !$res ) return null;

      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      return $data;
    }

    public function GetRecent(int $maxCount = 10) {
      $query = "SELECT u.name AS username, h.itemtype, h.itemid, h.action, h.timestamp, h.field, h.newvalue, h.oldvalue FROM history h LEFT JOIN users u ON h.userid = u.id  ORDER BY h.id DESC LIMIT $maxCount;";
      $res = $this->db()->sql->query($query) or \Log::WarningSQLQuery($query, $this->db()->sql);
      if( !$res ) return null;

      $data = $res->fetch_all(MYSQLI_ASSOC);
      $res->free();

      return $data;
    }

    public function PrintPartElementHistoryData($data = null) {
      $dataStr = "";
      $langStr = "";
      $attrEls = array();
      switch( $data['action'] ) {
        case 'edit':
          switch( $data['field'] ) {
            case 'storelocation':
              $langStr = ":historyUserPartChangeStorageLocation:hUser:hOldVal:hNewVal";
              $attrEls = array(
                'hUser'     => $data['username'],
                'hOldVal' => $data['oldvalue'],
                'hNewVal' => $data['newvalue']
              );
              break;
            case 'footprint':
              $langStr = ":historyUserPartChangeFootprint:hUser:hOldVal:hNewVal";
              $attrEls = array(
                'hUser'     => $data['username'],
                'hOldVal' => $data['oldvalue'],
                'hNewVal' => $data['newvalue']
              );
              break;
            case 'name':
              $langStr = ":historyUserPartChangeName:hUser:hOldVal:hNewVal";
              $attrEls = array(
                'hUser'     => $data['username'],
                'hOldVal' => $data['oldvalue'],
                'hNewVal' => $data['newvalue']
              );
              break;
            case 'price':
              $langStr = ":historyUserPartChangePrice:hUser:hOldVal:hNewVal";
              $attrEls = array(
                'hUser'     => $data['username'],
                'hOldVal' => $this->db()->Parts()->FormatPrice($data['oldvalue']),
                'hNewVal' => $this->db()->Parts()->FormatPrice($data['newvalue'])
              );
              break;
            case 'historyUserPartChangePartNumber':
              $langStr = ":historyUserPartChangePrice:hUser:hOldVal:hNewVal";
              $attrEls = array(
                'hUser'     => $data['username'],
                'hOldVal' => $data['oldvalue'],
                'hNewVal' => $data['newvalue']
              );
              break;
            case 'totalstock':
              $langStr = ":historyUserPartChangeTotalStock:hUser:hOldVal:hNewVal";
              $attrEls = array(
                'hUser'     => $data['username'],
                'hOldVal' => $data['oldvalue'],
                'hNewVal' => $data['newvalue']
              );
              break;
            case 'instock':
              $langStr = ":historyUserPartChangeInStock:hUser:hOldVal:hNewVal";
              $attrEls = array(
                'hUser'     => $data['username'],
                'hOldVal' => $data['oldvalue'],
                'hNewVal' => $data['newvalue']
              );
              break;
            case 'mininstock':
              $langStr = ":historyUserPartChangeMinStock:hUser:hOldVal:hNewVal";
              $attrEls = array(
                'hUser'     => $data['username'],
                'hOldVal' => $data['oldvalue'],
                'hNewVal' => $data['newvalue']
              );
              break;
            case 'supplier':
              $langStr = ":historyUserPartChangeSupplier:hUser:hOldVal:hNewVal";
              $attrEls = array(
                'hUser'     => $data['username'],
                'hOldVal' => $data['oldvalue'],
                'hNewVal' => $data['newvalue']
              );
              break;
            case 'comment':
              $langStr = ":historyUserPartChangeDescription:hUser";
              $attrEls = array(
                'hUser'     => $data['username']
              );
              break;
            default:
              return "";
          }
          break;
        case 'delete':
          $langStr = ":historyUserPartDelete:hUser:hPartName";
          $attrEls = array(
            'hUser'     => $data['username'],
            'hPartName' => $data['name']
          );
          break;
        case 'add':
          $langStr = ":historyUserPartAdd:hUser:hPartName";
          $attrEls = array(
            'hUser'     => $data['username'],
            'hPartName' => $data['name']
          );
          break;
        default:
          $dataStr = $data['username'].' '.$data['method'];
          break;
      }
      array_walk($attrEls, function(&$el, $attr) {
        $el = "$attr=\"".htmlspecialchars($el)."\"";
      });
      return "<td>".$data['timestamp']."</td><td ".join(" ", $attrEls)." uilang=\"$langStr\">$dataStr</td>";
    }

    public function PrintHistoryData($data = null) {
      if( $data === null )
        return "";

      $s = '<table class="table-stroke ui-shadow ui-responsive" data-mode="reflow" data-role="table">'."\n";
      $s .= "<thead><tr><th><h4 uilang=\"date\"></h4></th><th><h4 uilang=\"description\"></h4></th></tr></thead>";
      $s .= '<tbody>';
      $first = true;
      foreach( $data as $el ) {

        if( $el['itemtype'] == 'P' ) {
          $s .= "<tr>\n";
          $s .= $this->PrintPartElementHistoryData($el);
          $s .= "</tr>\n";
        }
      }
      $s .= "</tbody></table>\n";

      return $s;
    }

    public function PrintSimpleHistoryData($data = null) {
      if( $data === null )
        return "";

      $s = '<table class="table-stroke ui-shadow ui-responsive" data-mode="reflow" data-role="table">'."\n";
      $first = true;
      foreach( $data as $el ) {

        if( $first ) {
          $s .= "<thead><tr>\n";
            foreach( $el as $col => $value ) {
              $s .= "<td><h4>".htmlspecialchars($col)."</h4></td>\n";
            }
            $first = false;
            $s .= "</tr></thead><tbody><tr>";
        }
        foreach( $el as $col => $value ) {

          $s .= "<td>".htmlspecialchars($value)."</td>\n";
        }
        $s .= "</tr>\n";
      }
      $s .= "</tbody></table>\n";

      return $s;
    }
  }
}

?>
