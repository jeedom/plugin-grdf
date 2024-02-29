<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

require_once __DIR__  . '/../../../../core/php/core.inc.php';

class grdf extends eqLogic {

  public static function getConfigForCommunity($_separator = '<br>') {
    $return = '';
    foreach (self::byType(__CLASS__, true) as $meter) {
      $return .= '- **' . $meter->getName() . '**' . $_separator;
      $return .= '  - ' . __('Type de compteur', __FILE__) . ' : ' . $meter->getConfiguration('reading_frequency') . $_separator;
      $return .= '  - ' . __('Date de dernière publication', __FILE__) . ' : ' . $meter->getConfiguration('reading_date') . $_separator;
      $return .= '  - ' . __("Droits d'accès", __FILE__) . ' :' . $_separator;
      foreach ($meter->getConfiguration('access_rights') as $access => $right) {
        $return .= '    - ' . $access . ' : ' . $right . $_separator;
      }
    }
    return $return;
  }

  public static function cronDaily() {
    if (!is_object($cron = cron::byClassAndFunction(__CLASS__, 'pull')) || strtotime($cron->getNextRunDate()) > strtotime('+1 day')) {
      self::schedule('today');
    }
  }

  public static function pull() {
    log::add(__CLASS__, 'info', '*** ' . __('Vérification automatique des compteurs GRDF', __FILE__) . ' ***');
    foreach (self::byType(__CLASS__, true) as $meter) {
      $pause = mt_rand(1, 59);
      log::add(__CLASS__, 'debug', $meter->getHumanName() . ' ' . __('Pause de', __FILE__) . ' ' . $pause . ' ' . __('secondes', __FILE__));
      sleep($pause);
      $meter->refreshData();
    }
    self::schedule();
  }

  private static function schedule(string $_prog = '+1 day') {
    if (!is_object($cron = cron::byClassAndFunction(__CLASS__, 'pull'))) {
      $cron = (new cron)
        ->setClass(__CLASS__)
        ->setFunction('pull')
        ->setTimeout(25);
    }
    $cron->setSchedule(cron::convertDateToCron(strtotime($_prog . ' '  . mt_rand(9, 19) . ':' . mt_rand(1, 59))))->save();
    log::add(__CLASS__, 'info', '*** ' . __('Prochaine vérification automatique des compteurs GRDF', __FILE__) . ' : ' . $cron->getNextRunDate() . ' ***');
  }

  public function preInsert() {
    $this->setCategory('energy', 1);
    $this->setIsEnable(1);
    $this->setIsVisible(1);
    $this->setConfiguration('measure_type', 'consos');
  }

  public function preUpdate() {
    if ($this->getIsEnable() == 1) {
      $pceId = str_replace(' ', '', $this->getConfiguration('pce_id'));
      if (empty($pceId)) {
        throw new Exception(__("Le numéro d'identification du PCE doit être renseigné", __FILE__));
      }
      if (!(strlen($pceId) == 14 || strlen($pceId) == 8 && substr($pceId, 0, 2) == 'GI')) {
        throw new Exception(__("Le numéro d'identification du PCE doit être composé de 14 chiffres ou des lettres GI suivies de 6 chiffres", __FILE__));
      }
      $this->setConfiguration('pce_id', $pceId);
    }
  }

  public function postUpdate() {
    if ($this->getIsEnable() == 1) {
      $refreshCmd = $this->getCmd('action', 'refresh');
      if (!is_object($refreshCmd)) {
        $refreshCmd = (new grdfCmd)
          ->setLogicalId('refresh')
          ->setEqLogic_id($this->getId())
          ->setName(__('Rafraîchir', __FILE__))
          ->setType('action')
          ->setSubType('other')
          ->setOrder(99)
          ->save();
      }

      $this->refreshData();
    }
  }

  public function refreshData() {
    log::add(__CLASS__, 'debug', $this->getHumanName() . ' -----------------------------------------------------------------------');
    if ($this->updateRightsAndFrequency()) {
      $frequency = $this->getConfiguration('reading_frequency');
      $lastPub = $this->getConfiguration('reading_date');

      switch ($frequency) {
        case '6M':
          if (empty($lastPub) || strtotime($lastPub) < strtotime('-1 month 00:00') && in_array(date('d'), [date('d', strtotime('+2 days ' . $lastPub)), date('d', strtotime('+3 days ' . $lastPub))])) {
            $this->computeData('donnees_consos_publiees', $frequency, $lastPub);
          } else {
            log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __('Les données semestrielles publiées sont à jour, aucune action', __FILE__));
          }
          break;

        case '1M':
          if (empty($lastPub) || strtotime($lastPub) < strtotime('-1 month 00:00') && in_array(date('d'), [date('d', strtotime('+2 days ' . $lastPub)), date('d', strtotime('+3 days ' . $lastPub))])) {
            $this->computeData('donnees_consos_publiees', $frequency, $lastPub);
          } else {
            log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __('Les données mensuelles publiées sont à jour, aucune action', __FILE__));
          }

          $lastInf = $this->getConfiguration('reading_date_temp');
          if (empty($lastInf) || strtotime($lastInf) < strtotime('-1 day 00:00')) {
            $this->computeData('donnees_consos_informatives', $frequency, $lastInf);
          } else {
            log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __('Les données quotidiennes informatives sont à jour, aucune action', __FILE__));
          }
          break;

        case 'MM':
          if (empty($lastPub) || strtotime($lastPub) < strtotime('-1 month 00:00') && date('d') >= date('d', strtotime('+2 days ' . $lastPub)) && date('d') <= date('d', strtotime('+7 weekdays ' . date('Y-m-01')))) {
            $directions = ($this->getConfiguration('measure_type') != 'both') ? [$this->getConfiguration('measure_type', 'consos')] : ['consos', 'injections'];
            foreach ($directions as $direction) {
              $this->computeData('donnees_' . $direction . '_publiees', $frequency, $lastPub);
            }
          } else {
            log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __('Les données mensuelles publiées sont à jour, aucune action', __FILE__));
          }

          $lastInf = $this->getConfiguration('reading_date_temp');
          if (empty($lastInf) || strtotime($lastInf) < strtotime('-1 month 00:00') && date('d') >= 10 && date('d') <= 20) {
            $this->computeData('donnees_consos_informatives', $frequency, $lastInf);
          } else {
            log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __('Les données quotidiennes informatives sont à jour, aucune action', __FILE__));
          }
          break;

        case 'JJ':
          if (empty($lastPub) || strtotime($lastPub) < strtotime('-1 day 00:00')) {
            $directions = ($this->getConfiguration('measure_type') != 'both') ? [$this->getConfiguration('measure_type', 'consos')] : ['consos', 'injections'];
            foreach ($directions as $direction) {
              $this->computeData('donnees_' . $direction . '_publiees', $frequency, $lastPub);
            }
          } else {
            log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __('Les données quotidiennes publiées sont à jour, aucune action', __FILE__));
          }
          break;

        default:
          log::add(__CLASS__, 'error', $this->getHumanName() . ' ' . __("Type de compteur inconnu", __FILE__) . ' : ' . $this->getConfiguration('reading_frequency'));
          break;
      }
    }
    log::add(__CLASS__, 'debug', $this->getHumanName() . ' -----------------------------------------------------------------------');
  }

  private function computeData(string $_url, string $_frequency, string $_lastRead) {
    $explodeUrl = explode('_', $_url);
    $direction = array(
      'short' => str_replace(['consos', 'injections'], ['conso', 'inj'], $explodeUrl[1]),
      'long' => str_replace(['consos', 'injections'], ['consommation', 'injection'], $explodeUrl[1]),
      'mix' => rtrim($explodeUrl[1], 's')
    );
    $dates = array(
      'start' => (empty($_lastRead)) ? $this->getConfiguration('access_rights')['perim_donnees_' . $direction['short'] . '_debut'] : date('Y-01-01', strtotime($_lastRead)),
      'end' => date('Y-m-d')
    );

    if ($this->controlAccessRight('perim_donnees_' . $explodeUrl[2], $direction['short'], $dates)) {
      $formatedData = array();
      sleep(1);
      $datas = $this->callGRDF('/adict/v2/pce/#pce_id#/' . $_url . '?date_debut=' . $dates['start'] . '&date_fin=' . $dates['end']);
      if (!isset($datas[0]['pce'])) {
        $datas = [$datas];
      }
      foreach ($datas as $data) {
        if (!empty($data[$direction['long']])) {
          $data = $data[$direction['long']];
          $status = str_replace(['Provisoire', 'Définitive'], ['_temp', ''], $data['statut_' . $direction['mix']]);
          $beginTime = strtotime($data['date_debut_' . $direction['long']]);
          $endTime = strtotime('-1 day ' . $data['date_fin_' . $direction['long']]);
          $endDate = date('Y-m-d', $endTime);

          if ($endDate == date('Y-m-d', $beginTime)) {
            $formatedData['daily_' . $direction['short'] . $status][] = array(
              'value' => $data['energie'],
              'date' => $endDate
            );
            $formatedData['monthly_' . $direction['short'] . $status][date('Y', $beginTime)][date('m', $beginTime)][] = array(
              'value' => $data['energie'],
              'date' => $endDate
            );
            if (empty($status) && in_array($_frequency, ['1M', 'JJ'])) {
              $formatedData['daily_' . $direction['short'] . '_temp'][] = array(
                'value' => $data['energie'],
                'date' => $endDate
              );
              $formatedData['monthly_' . $direction['short'] . '_temp'][date('Y', $beginTime)][date('m', $beginTime)][] = array(
                'value' => $data['energie'],
                'date' => $endDate
              );
            }
          } else if ($endTime <= strtotime('+1 month ' . $data['date_debut_' . $direction['long']])) {
            $formatedData['monthly_' . $direction['short'] . $status][date('Y', $beginTime)][date('m', $beginTime)][] = array(
              'value' => $data['energie'],
              'date' => $endDate
            );
          } else {
            $formatedData['semi-annually_' . $direction['short'] . $status][] = array(
              'value' => $data['energie'],
              'date' => $endDate
            );
          }
        } else {
          log::add(__CLASS__, 'warning', $this->getHumanName() . ' ' . __("Données absentes en", __FILE__) . ' ' . $direction['long'] . ' : ' . print_r($data, true));
        }
      }

      if (!empty($formatedData)) {
        // log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __("Données en cours d'enregistrement", __FILE__) . ' : ' . print_r($formatedData, true));
        $dataType = str_replace(['informatives', 'publiees'], ['_temp', ''], $explodeUrl[2]);
        foreach (array_keys($formatedData) as $cmdLogical) {
          $cmd = $this->getGRDFCmd('info', $cmdLogical);
          $explodeLogical = explode('_', $cmdLogical);
          if ($explodeLogical[0] == 'monthly') {
            $yearCmd = $this->getGRDFCmd('info', str_replace('month', 'year', $cmdLogical));
            $countYears = count($formatedData[$cmdLogical]);
            $currentYear = 1;
            foreach ($formatedData[$cmdLogical] as $year => $months) {
              $yearSum = 0;
              $countMonths = count($months);
              $currentMonth = 1;
              foreach ($months as $month => $datas) {
                $monthSum = 0;
                $countDatas = count($datas);
                foreach ($datas as $i => $data) {
                  $monthSum += $data['value'];
                  $yearSum += $data['value'];
                  if ($i + 1 == $countDatas && $currentMonth == $countMonths && $currentYear == $countYears) {
                    $cmd->recordData($monthSum, $data['date'], true);
                    $yearCmd->recordData($yearSum, $data['date'], true);
                    if (empty($dataType) && in_array($_frequency, ['1M', 'MM'])) {
                      $this->setConfiguration('reading_date', date('Y-m-d', strtotime('+1 day ' . $data['date'])))->save(true);
                    }
                  } else {
                    $cmd->recordData($monthSum, $data['date']);
                    $yearCmd->recordData($yearSum, $data['date']);
                  }
                }
                $currentMonth++;
              }
              $currentYear++;
            }
          } else {
            $countDatas = count($formatedData[$cmdLogical]);
            foreach ($formatedData[$cmdLogical] as $i => $data) {
              if ($i + 1 == $countDatas) {
                $cmd->recordData($data['value'], $data['date'], true);
                if (!($_frequency == 'JJ' && isset($explodeLogical[2]))) {
                  $this->setConfiguration('reading_date' . $dataType, date('Y-m-d', strtotime('+1 day ' . $data['date'])))->save(true);
                }
              } else {
                $cmd->recordData($data['value'], $data['date']);
              }
            }
          }
        }
      }
    }
  }

  private function getGRDFCmd(string $_type = null, string $_logicalId = null) {
    $cmd = $this->getCmd($_type, $_logicalId);
    if (!is_object($cmd)) {
      $cmdsTemplate = array(
        'daily_conso' => ['name' => __('Consommation quotidienne', __FILE__), 'order' => 0],
        'daily_conso_temp' => ['name' => __('Consommation quotidienne estimée', __FILE__), 'order' => 1],
        'monthly_conso' => ['name' => __('Consommation mensuelle', __FILE__), 'order' => 2],
        'monthly_conso_temp' => ['name' => __('Consommation mensuelle estimée', __FILE__), 'order' => 3],
        'semi-annually_conso' => ['name' => __('Consommation semestrielle', __FILE__), 'order' => 4],
        'yearly_conso' => ['name' => __('Consommation annuelle', __FILE__), 'order' => 5],
        'yearly_conso_temp' => ['name' => __('Consommation annuelle estimée', __FILE__), 'order' => 6],
        'daily_inj' => ['name' => __('Injection quotidienne', __FILE__), 'order' => 7],
        'daily_inj_temp' => ['name' => __('Injection quotidienne estimée', __FILE__), 'order' => 8],
        'monthly_inj' => ['name' => __('Injection mensuelle', __FILE__), 'order' => 9],
        'monthly_inj_temp' => ['name' => __('Injection mensuelle estimée', __FILE__), 'order' => 10],
        'yearly_inj' => ['name' => __('Injection annuelle', __FILE__), 'order' => 11],
        'yearly_inj_temp' => ['name' => __('Injection annuelle estimée', __FILE__), 'order' => 12]
      );
      if (in_array($_logicalId, array_keys($cmdsTemplate))) {
        $cmd = (new grdfCmd)
          ->setEqLogic_id($this->getId())
          ->setLogicalId($_logicalId)
          ->setName($cmdsTemplate[$_logicalId]['name'])
          ->setType('info')
          ->setSubType('numeric')
          ->setTemplate('dashboard', 'tile')
          ->setTemplate('mobile', 'tile')
          ->setDisplay('showStatsOndashboard', 0)
          ->setDisplay('showStatsOnmobile', 0)
          ->setDisplay('graphType', 'column')
          ->setUnite('kWh')
          ->setOrder($cmdsTemplate[$_logicalId]['order'])
          ->setIsVisible(1)
          ->setIsHistorized(1);
        $cmd->save();
      }
    }
    return $cmd;
  }

  private function callGRDF(string $_path, array $_post = null) {
    log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __('Appel API GRDF', __FILE__) . ' : ' . $_path);
    $_path = str_replace('#pce_id#', $this->getConfiguration('pce_id'), $_path);
    try {
      $url = config::byKey('service::cloud::url') . '/service/grdf?path=' . urlencode($_path);
      $request_http = new com_http($url);
      $request_http->setHeader(array('Content-Type: application/json', 'Autorization: ' . sha512(mb_strtolower(config::byKey('market::username')) . ':' . config::byKey('market::password'))));
      if ($_post) {
        $request_http->setPost(json_encode($_post));
      }
      $result = json_decode($request_http->exec(30, 1), true);
      if (!is_array($result)) {
        $datas = array();
        foreach (array_filter(explode("\n", $result)) as $json) {
          $datas[] = json_decode($json, true);
        }
        $result = $datas;
      }
    } catch (exception $e) {
      $result = array('error' => $e);
    }
    // log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __('Retour appel API GRDF', __FILE__) . ' : ' . print_r($result, true));
    return $result;
  }

  private function updateRightsAndFrequency() {
    log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __("Mise à jour des autorisations d'accès en cours...", __FILE__));
    $accessRights = $this->callGRDF('/adict/v2/droits_acces', array('id_pce' => [$this->getConfiguration('pce_id')]));
    $lastConsent = $accessRights[count($accessRights) - 2];
    if (isset($lastConsent['etat_droit_acces'])) {
      $filter = array('etat_droit_acces', 'date_debut_droit_acces', 'date_fin_droit_acces', 'perim_donnees_contractuelles', 'perim_donnees_techniques', 'perim_donnees_informatives', 'perim_donnees_publiees', 'perim_donnees_conso_debut', 'perim_donnees_conso_fin', 'perim_donnees_inj_debut', 'perim_donnees_inj_fin');
      $neededRights = array_intersect_key($lastConsent, array_fill_keys($filter, null));
      // log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . print_r($neededRights, true));
      $this->setConfiguration('access_rights', $neededRights);

      if (date('d') == date('d', strtotime($this->getConfiguration('reading_date', 'today')))) {
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __("Mise à jour de la fréquence de relève des données en cours...", __FILE__));
        if ($this->controlAccessRight('perim_donnees_techniques')) {
          sleep(1);
          $technicalData = $this->callGRDF('/adict/v2/pce/#pce_id#/donnees_techniques');
          if (isset($technicalData['donnees_techniques']['caracteristiques_compteur']['frequence'])) {
            // log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' .  $technicalData['donnees_techniques']['caracteristiques_compteur']['frequence']);
            $this->setConfiguration('reading_frequency', $technicalData['donnees_techniques']['caracteristiques_compteur']['frequence']);
          } else {
            log::add(__CLASS__, 'error', $this->getHumanName() . ' ' . __("Impossible de vérifier la fréquence de relève des données", __FILE__) /*. ' : ' . print_r($technicalData, true)*/);
          }
        } else {
          log::add(__CLASS__, 'warning', $this->getHumanName() . ' ' . __("Impossible de vérifier la fréquence de relève des données. Vérifiez les autorisations d'accès", __FILE__) /*. ' : ' . print_r($accessRights, true)*/);
        }
      }
      $this->save(true);
    } else {
      log::add(__CLASS__, 'error', $this->getHumanName() . ' ' . __("Impossible de vérifier les autorisations d'accès à l'API GRDF", __FILE__) /*. ' : ' . print_r($accessRights, true)*/);
      return false;
    }
    return true;
  }

  private function controlAccessRight(string $_perimeter, string $_direction = 'conso', array $_dates = array()) {
    $accessRights = $this->getConfiguration('access_rights', array());
    if (!isset($accessRights['etat_droit_acces']) || $accessRights['etat_droit_acces'] != 'Active') {
      log::add(__CLASS__, 'error', $this->getHumanName() . ' ' . __("Erreur d'autorisation d'accès à l'API GRDF", __FILE__) . ' : ' . $accessRights['etat_droit_acces']);
      return false;
    }
    $time = time();
    if (strtotime($accessRights['date_debut_droit_acces']) > $time || strtotime($accessRights['date_fin_droit_acces']) < $time) {
      log::add(__CLASS__, 'error', $this->getHumanName() . ' ' . __("Erreur dans les dates d'autorisation d'accès à l'API GRDF", __FILE__) . ' : ' . $accessRights['date_debut_droit_acces'] . ' => ' . $accessRights['date_fin_droit_acces'] . ' != ' . date('Y-m-d'));
      return false;
    }
    if ($accessRights[$_perimeter] != 'Vrai') {
      log::add(__CLASS__, 'warning', $this->getHumanName() . ' ' . __("Erreur d'autorisation d'accès à l'API GRDF pour le périmètre", __FILE__) . ' : ' . $_perimeter);
      return false;
    }
    if (in_array($_perimeter, ['perim_donnees_informatives', 'perim_donnees_publiees'])) {
      if (strtotime($accessRights['perim_donnees_' . $_direction . '_debut']) > strtotime($_dates['start']) || strtotime($accessRights['perim_donnees_' . $_direction . '_fin']) < strtotime($_dates['end'])) {
        log::add(__CLASS__, 'warning', $this->getHumanName() . ' ' . __("Erreur dans les dates d'autorisation d'accès à l'API GRDF pour le périmètre", __FILE__) . ' : ' . $_perimeter . ' ' . $_direction . ' (' . $accessRights['perim_donnees_' . $_direction . '_debut'] . ' => ' . $accessRights['perim_donnees_' . $_direction . '_fin'] . ' != ' . $_dates['start'] . ' => ' . $_dates['end'] . ')');
        return false;
      }
    }
    log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __("Autorisation d'accès à l'API GRDF validée pour le périmètre", __FILE__) . ' : ' . $_perimeter);
    return true;
  }
}

class grdfCmd extends cmd {

  public function dontRemoveCmd() {
    return true;
  }

  public function execute($_options = array()) {
    if ($this->getLogicalId() == 'refresh') {
      $this->getEqLogic()->refreshData();
    }
  }

  public function recordData(int $_value, string $_date, bool $_event = false) {
    $datetime = strtotime($_date);
    if (empty($this->getHistory(date('Y-m-d 00:00:00', $datetime), date('Y-m-d 23:59:59', $datetime)))) {
      if ($_event) {
        log::add('grdf', 'debug', $this->getHumanName() . ' ' . __('Mise à jour de la valeur', __FILE__) . ' : ' . $_date . ' => ' . $_value . ' ' . $this->getUnite());
        $this->event($_value, $_date);
      } else if ($this->getIsHistorized() == 1) {
        log::add('grdf', 'debug', $this->getHumanName() . ' ' . __('Enregistrement historique', __FILE__) . ' : ' . $_date . ' => ' . $_value . ' ' . $this->getUnite());
        $this->addHistoryValue($_value, $_date);
      }
    }
  }
}
