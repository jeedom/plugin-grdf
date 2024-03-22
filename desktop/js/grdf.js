/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

function printEqLogic(_eqLogic) {
  document
    .querySelectorAll(".hide-" + _eqLogic.configuration.measure_type)
    .forEach((_toHide) => {
      _toHide.style.display = "none";
    }); // 4.4 mini => document.querySelectorAll('.hide-' + _eqLogic.configuration.measure_type).unseen()
  document
    .querySelectorAll(".show-" + _eqLogic.configuration.reading_frequency)
    .forEach((_toShow) => {
      _toShow.style.display = "";
    }); // 4.4 mini => document.querySelectorAll('.show-' + _eqLogic.configuration.reading_frequency).seen()
}

$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true,
}); // 4.4 mini => useless

function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = { configuration: {} };
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {};
  }
  var tr = '<td class="hidden-xs">';
  tr += '<span class="cmdAttr" data-l1key="id"></span>';
  tr +=
    '<input class="cmdAttr form-control input-sm" data-l1key="type" value="info" style="display:none;">';
  tr +=
    '<input class="cmdAttr form-control input-sm" data-l1key="subType" value="numeric" style="display:none;">';
  tr += "</td>";
  tr += "<td>";
  tr += '<div class="input-group">';
  tr +=
    '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">';
  tr += '<span class="input-group-btn">';
  tr +=
    '<a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a>';
  tr += "</span>";
  tr +=
    '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>';
  tr += "</div>";
  tr += "</td>";
  tr += "<td>";
  tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
  tr += "</td>";
  tr += "<td>";
  tr +=
    '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked>{{Afficher}}</label> ';
  tr +=
    '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked>{{Historiser}}</label> ';
  tr +=
    '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;margin-top:7px;">';
  tr += "</td>";
  tr += "<td>";
  if (is_numeric(_cmd.id)) {
    tr +=
      '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
    tr +=
      '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
  }
  tr +=
    '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i>';
  tr += "</td>";

  let newRow = document.createElement("tr");
  newRow.innerHTML = tr;
  newRow.classList = "cmd";
  newRow.setAttribute("data-cmd_id", init(_cmd.id));
  document
    .getElementById("table_cmd")
    .querySelector("tbody")
    .appendChild(newRow);
  jQuery(newRow).setValues(_cmd, ".cmdAttr"); // 4.4 mini => newRow.setJeeValues(_cmd, '.cmdAttr')
  jeedom.cmd.changeType(jQuery(newRow), init(_cmd.subType)); // 4.4 mini => jeedom.cmd.changeType(newRow, init(_cmd.subType))
}
