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

var oBtSup = [null,null];
var nbr = 0;

$("#bt_addeeBsmlvgl").on('click', function (event) {
    var r = document.querySelector(':root');
  	var rs = getComputedStyle(r);
  	var button = document.getElementById('eebsmboutontester');
  	button.style.backgroundColor = rs.getPropertyValue('--al-warning-color');
  
  	document.getElementById('eebsmip').disabled = false;
    document.getElementById('eebsmmdp').disabled = false;
    $("#eebsmboutontester").show();
  
    $("#eebsmemplacement").hide();
    $("#diveebsmname").hide();
    $("#diveebsmmin").hide();
    $("#diveebsmmax").hide();
    $("#diveebsmval").hide();
    $("#diveebsmunit").hide();
    $("#eebsmboutonenvoyer").hide();
    $("#eebsmbuttonajouter").hide();
    $("#labelcmd").hide();
    $("#eebsmtype").hide();
    $("#eebsmtable").hide();    
    $("#diveebsmlist").hide();
     	
    $("#PopupDiv").show();  
})

$("#eebsmboutontester").on('click', function (event) {
  	
    var ip = $("#eebsmip").val();
    var mdp = $("#eebsmmdp").val();
    var url = 'http://'+ip+'/testmodule?key='+mdp;
    HTTPTestModule(url,ip,mdp);
   
})

function HTTPTestModule(url,ip,mdp){
  $.ajax({
    type: 'POST',
    url: 'plugins/eebsm/core/ajax/eebsm.ajax.php',
    data: {
      action: 'GetHTTP',
      url: url,

    },
    dataType: 'json',
    error: function(request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function(data) {
      if (data.result == 'Tout est OK'){
        var r = document.querySelector(':root');
        var rs = getComputedStyle(r);
        var button = document.getElementById('eebsmboutontester');
        button.style.backgroundColor = rs.getPropertyValue('--bt-success-color');
        $("#labelcmd").show();
        $("#eebsmtype").show();
        $("#eebsmtable").show();
        document.getElementById('eebsmip').disabled = true;
 		document.getElementById('eebsmmdp').disabled = true;
        $("#eebsmboutontester").hide();
        var urlget = 'http://'+ip+'/getlvgl?key='+mdp;
  		RempHTTPPost(urlget);
  
      }else{
        var r = document.querySelector(':root');
        var rs = getComputedStyle(r);
        var button = document.getElementById('eebsmboutontester');
        button.style.backgroundColor = rs.getPropertyValue('--al-danger-color');
      }
         
    }     
  })      
}



function HTTPTestModuleSend(url,ip,mdp){
  $.ajax({
    type: 'POST',
    url: 'plugins/eebsm/core/ajax/eebsm.ajax.php',
    data: {
      action: 'GetHTTP',
      url: url,

    },
    dataType: 'json',
    error: function(request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function(data) {
      if (data.result == 'Tout est OK'){
        var r = document.querySelector(':root');
        var rs = getComputedStyle(r);
        var button = document.getElementById('eebsmboutontester');
        button.style.backgroundColor = rs.getPropertyValue('--bt-success-color');
        SendListHTTP(ip,mdp);
      }else{
        var r = document.querySelector(':root');
        var rs = getComputedStyle(r);
        var button = document.getElementById('eebsmboutontester');
        button.style.backgroundColor = rs.getPropertyValue('--al-danger-color');
        alert("Oups! N'y aurait-il pas un petit problème dans les informations d'identification saisies?");
      }
         
    }     
  })      
}

$("#eebsmboutonenvoyer").on('click', function (event) {
  var ip = $("#eebsmip").val();
    var mdp = $("#eebsmmdp").val();
    var url = 'http://'+ip+'/testmodule?key='+mdp;
    HTTPTestModuleSend(url,ip,mdp);
  
})
                            
                            
function SendListHTTP(ip,mdp){
   var urlremove = 'http://'+ip+'/removelistlvgl?key='+mdp;
  var resultremove = HTTPPost(urlremove);
  
  var eebsmtable = document.getElementById('eebsmtable');
  
  for (let i = 2; i < eebsmtable.rows.length; i++) {
    
    var eebsmtype = eebsmtable.rows[i].cells[0].innerText;
  	var eebsmemplacement = eebsmtable.rows[i].cells[1].innerText;
  	var eebsmname = eebsmtable.rows[i].cells[2].innerText;
    var eebsmunit = eebsmtable.rows[i].cells[3].innerText;
    var eebsmmin = eebsmtable.rows[i].cells[4].innerText;
    var eebsmmax = eebsmtable.rows[i].cells[5].innerText;
    var eebsmval = eebsmtable.rows[i].cells[6].innerText;
    
    var url = encodeURI('http://'+ip+'/addlistlvgl?key='+mdp+'&type='+eebsmtype+'&name='+eebsmname+'&loc='+eebsmemplacement+'&min='+eebsmmin+'&max='+eebsmmax+'&value='+eebsmval+'&unit='+eebsmunit);
    var result = HTTPPost(url);    
  }
    var url = 'http://'+ip+'/savelistlvgl?key='+mdp;
    var result = HTTPPost(url);
  
  	Vider();
}

function HTTPPost(url){
  $.ajax({
    async: false,
    type: 'POST',
    url: 'plugins/eebsm/core/ajax/eebsm.ajax.php',
    data: {
      action: 'GetHTTP',
      url: url,

    },
    dataType: 'json',
    error: function(request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function(data) {
      //alert(data.result);
    return data.result;      
    }     
  })  
  //sleep(200);  
}


function RempHTTPPost(url){  
  $.ajax({
    type: 'POST',
    url: 'plugins/eebsm/core/ajax/eebsm.ajax.php',
    data: {
      action: 'GetHTTP',
      url: url,

    },
    dataType: 'json',
    error: function(request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function(data) {  
        Remplissage(data.result); 
    }     
  })
}


function Vider(){
  document.getElementById('eebsmip').disabled = false;
  document.getElementById('eebsmmdp').disabled = false;
  $("#eebsmboutontester").show();
  
  $("#labelcmd").hide();
  $("#eebsmtype").hide();
  $("#eebsmtable").hide();
  
  var r = document.querySelector(':root');
  var rs = getComputedStyle(r);
  var button = document.getElementById('eebsmboutontester');
  button.style.backgroundColor = rs.getPropertyValue('--al-warning-color');
  
  $("#eebsmip").val('');
  $("#eebsmmdp").val('');
  $("#eebsmname").val('');
  $("#eebsmmin").val('');
  $("#eebsmmax").val('');
  $("#eebsmval").val('');
  $("#eebsmunit").val(''); 
  $("#eebsmtype").val('nope'); 
  
  
  $("#eebsmemplacement").hide();
  $("#diveebsmname").hide();
  $("#diveebsmmin").hide();
  $("#diveebsmmax").hide();
  $("#diveebsmval").hide();
  $("#diveebsmunit").hide();
  $("#diveebsmlist").hide();
  $("#eebsmboutonenvoyer").hide();
  $("#eebsmbuttonajouter").hide();
  
  var eebsmtable = document.getElementById('eebsmtable'); 
  if (eebsmtable.rows.length>2){
    var taille = eebsmtable.rows.length;
    for (let i = 2; i < taille; i++) { 
      var eebsmname = eebsmtable.rows[2].cells[2].innerText;      
      eebsmtable.deleteRow(2);

    }
  }
  nbr = 0;
}


function Remplissage(contjson){
  const obj = JSON.parse(contjson);
  var keyCount  = Object.keys(obj["lvgl"]).length;
  
    	
  for (let i = 0; i < keyCount; i++) {
    
    var eebsmemplacement = obj["lvgl"][i]["emplacement"];
  	var eebsmtype = obj["lvgl"][i]["type"];
  	var eebsmname = obj["lvgl"][i]["name"];
  	var eebsmmin = obj["lvgl"][i]["min"];
  	var eebsmmax = obj["lvgl"][i]["max"];
  	var eebsmval = obj["lvgl"][i]["value"];
  	var eebsmunit = obj["lvgl"][i]["unit"]; 
       
    var neebsmemplacement = document.createTextNode(eebsmemplacement);
    var neebsmtype = document.createTextNode(eebsmtype);
    var neebsmname = document.createTextNode(eebsmname);
    var neebsmmin = document.createTextNode(eebsmmin);
    var neebsmmax = document.createTextNode(eebsmmax);
    var neebsmval = document.createTextNode(eebsmval);
    var neebsmunit = document.createTextNode(eebsmunit);

    var eebsmtable = document.getElementById('eebsmtable');
    var eebsmrow = eebsmtable.insertRow();

    var eebsmcelltype = eebsmrow.insertCell(0);
    var eebsmcellemp = eebsmrow.insertCell(1);
    var eebsmcellname = eebsmrow.insertCell(2);
    var eebsmcellunit = eebsmrow.insertCell(3);
    var eebsmcellmin = eebsmrow.insertCell(4);
    var eebsmcellmax = eebsmrow.insertCell(5);
    var eebsmcellval = eebsmrow.insertCell(6);
    var eebsmcellsupp = eebsmrow.insertCell(7);

    eebsmcelltype.appendChild(neebsmtype);
    eebsmcellemp.appendChild(neebsmemplacement);  
    eebsmcellname.appendChild(neebsmname);  
    eebsmcellunit.appendChild(neebsmunit);  
    eebsmcellmin.appendChild(neebsmmin);  
    eebsmcellmax.appendChild(neebsmmax);  
    eebsmcellval.appendChild(neebsmval);
    eebsmcellsupp.innerHTML='<i class="bt-supprimer fas fa-minus-circle"></i>';
    
    oBtSup = eebsmtable.getElementsByClassName('bt-supprimer');
    oBtSup[i].addEventListener('click',  supprimerLigne); 
    nbr=i;
  }  
}


function supprimerLigne(oEvent){
  var oEleBt = oEvent.currentTarget,
  oTr = oEleBt.parentNode.parentNode ;
  oTr.remove();
  nbr--;
  $("#eebsmboutonenvoyer").show();
}

$("#eebsmbuttonannuler").on('click', function (event) {
  $("#PopupDiv").hide();
  Vider();
  
})




function sleep(miliseconds) {
   var currentTime = new Date().getTime();
   while (currentTime + miliseconds >= new Date().getTime()) {
   }
}



$("#eebsmbuttonajouter").on('click', function (event) {
  var eebsmemplacement = $("#emplacementSelect").val();
  var eebsmtype = $("#eebsmtype").val();
  var eebsmname = $("#eebsmname").val();
  var eebsmmin = $("#eebsmmin").val();
  var eebsmmax = $("#eebsmmax").val();
  
  var eebsmval = '';
  if (eebsmtype == 'list' || eebsmtype == 'listbutton') eebsmval = $("#eebsmlist").val();
  else eebsmval = $("#eebsmval").val();
  var eebsmunit = $("#eebsmunit").val(); 
  
  var eebsmtable = document.getElementById('eebsmtable');
  for (let i = 2; i < eebsmtable.rows.length; i++) {    
    var tableeebsmname = eebsmtable.rows[i].cells[2].innerHTML;
    if (tableeebsmname == eebsmname) {
      alert("Une commande existe déjà avec ce nom");  
      return
    }
    
  }
    
  
  if (eebsmemplacement == 'nope'){
   	alert("Veuillez choisir un emplacement à l'écran pour la commande créée");
    return
  }
  if (eebsmname == '' && eebsmtype != 'datetime'){
   	alert("Veuillez choisir un nom pour la commande créée");
    return
  }
  
  var neebsmemplacement = document.createTextNode(eebsmemplacement);
  var neebsmtype = document.createTextNode(eebsmtype);
  var neebsmname = document.createTextNode(eebsmname);
  var neebsmmin = document.createTextNode(eebsmmin);
  var neebsmmax = document.createTextNode(eebsmmax);
  var neebsmval = document.createTextNode(eebsmval);
  var neebsmunit = document.createTextNode(eebsmunit);
  
  var eebsmtable = document.getElementById('eebsmtable');
  var eebsmrow = eebsmtable.insertRow();
  
  var eebsmcelltype = eebsmrow.insertCell(0);
  var eebsmcellemp = eebsmrow.insertCell(1);
  var eebsmcellname = eebsmrow.insertCell(2);
  var eebsmcellunit = eebsmrow.insertCell(3);
  var eebsmcellmin = eebsmrow.insertCell(4);
  var eebsmcellmax = eebsmrow.insertCell(5);
  var eebsmcellval = eebsmrow.insertCell(6);
  var eebsmcellsupp = eebsmrow.insertCell(7);
  
  eebsmcelltype.appendChild(neebsmtype);
  eebsmcellemp.appendChild(neebsmemplacement);  
  eebsmcellname.appendChild(neebsmname);  
  eebsmcellunit.appendChild(neebsmunit);  
  eebsmcellmin.appendChild(neebsmmin);  
  eebsmcellmax.appendChild(neebsmmax);  
  eebsmcellval.appendChild(neebsmval);
  eebsmcellsupp.innerHTML='<i class="bt-supprimer fas fa-minus-circle"></i>';
    
  if (nbr<0) nbr=0;
  oBtSup = eebsmtable.getElementsByClassName('bt-supprimer');
  oBtSup[nbr].addEventListener('click',  supprimerLigne); 
  nbr++;
  
  $("#eebsmname").val('');
  $("#eebsmmin").val('');
  $("#eebsmmax").val('');
  $("#eebsmval").val('');
  $("#eebsmunit").val(''); 
  $("#eebsmlist").val('');
  $("#eebsmboutonenvoyer").show();
  
  
})


$("#eebsmtype").on('change',function(event) {
  
  var lvgl_type = $("#eebsmtype").val();
  if (lvgl_type == 'button' || lvgl_type == 'toggle' || lvgl_type == 'color'){
  	$("#eebsmmin").val('');
    $("#eebsmmax").val('');
    $("#eebsmval").val('');
    $("#eebsmunit").val(''); 
    $("#eebsmlist").val('');
  
    $("#eebsmemplacement").show();
    $("#diveebsmname").show();
    $("#diveebsmmin").hide();
    $("#diveebsmmax").hide();
    $("#diveebsmval").hide();
    $("#diveebsmunit").hide();
    $("#diveebsmlist").hide();
    $("#eebsmbuttonajouter").show();
  }else if (lvgl_type == 'range'){
  	$("#eebsmunit").val(''); 
    $("#eebsmlist").val('');
    
    $("#eebsmemplacement").show();
    $("#diveebsmname").show();
    $("#diveebsmmin").show();
    $("#diveebsmmax").show();
    $("#diveebsmval").show();
    $("#diveebsmunit").hide();
    $("#diveebsmlist").hide();
    $("#eebsmbuttonajouter").show();
  }else if (lvgl_type == 'roundrange'){
  	$("#eebsmlist").val('');
    
    $("#eebsmemplacement").show();
    $("#diveebsmname").show();
    $("#diveebsmmin").show();
    $("#diveebsmmax").show();
    $("#diveebsmval").show();
    $("#diveebsmunit").show();
    $("#diveebsmlist").hide();
    $("#eebsmbuttonajouter").show();
  }else if (lvgl_type == 'info'){
  	$("#eebsmmin").val('');
    $("#eebsmmax").val('');
    $("#eebsmval").val('');
    $("#eebsmlist").val('');
    
    $("#eebsmemplacement").show();
    $("#diveebsmname").show();
    $("#diveebsmmin").hide();
    $("#diveebsmmax").hide();
    $("#diveebsmval").hide();
    $("#diveebsmunit").show();
    $("#diveebsmlist").hide();
    $("#eebsmbuttonajouter").show();
  }else if (lvgl_type == 'datetime'){
  	$("#eebsmname").val('');
    $("#eebsmmin").val('');
    $("#eebsmmax").val('');
    $("#eebsmval").val('');
    $("#eebsmunit").val(''); 
    $("#eebsmlist").val('');
    
    $("#eebsmemplacement").show();
    $("#diveebsmname").hide();
    $("#diveebsmmin").hide();
    $("#diveebsmmax").hide();
    $("#diveebsmval").hide();
    $("#diveebsmunit").hide();
    $("#diveebsmlist").hide();
    $("#eebsmbuttonajouter").show();
  }else if (lvgl_type == 'list'){
  	$("#eebsmmin").val('');
    $("#eebsmmax").val('');
    $("#eebsmval").val('');
    $("#eebsmunit").val(''); 
    
    $("#eebsmemplacement").show();
    $("#diveebsmname").show();
    $("#diveebsmmin").hide();
    $("#diveebsmmax").hide();
    $("#diveebsmval").hide();
    $("#diveebsmunit").hide();
    $("#diveebsmlist").show();
    $("#eebsmbuttonajouter").show();
  }else if (lvgl_type == 'listbutton'){
  	$("#eebsmmin").val('');
    $("#eebsmmax").val('');
    $("#eebsmval").val('');
    $("#eebsmunit").val(''); 
    
    $("#eebsmemplacement").show();
    $("#diveebsmname").show();
    $("#diveebsmmin").hide();
    $("#diveebsmmax").hide();
    $("#diveebsmval").hide();
    $("#diveebsmunit").hide();
    $("#diveebsmlist").show();
    $("#eebsmbuttonajouter").show();
  }else{
    $("#eebsmname").val('');
    $("#eebsmmin").val('');
    $("#eebsmmax").val('');
    $("#eebsmval").val('');
    $("#eebsmunit").val(''); 
    $("#eebsmlist").val('');
    
    $("#eebsmbuttonajouter").hide();
    $("#eebsmemplacement").hide();
    $("#diveebsmname").hide();
    $("#diveebsmmin").hide();
    $("#diveebsmmax").hide();
    $("#diveebsmval").hide();
    $("#diveebsmunit").hide();
    $("#diveebsmlist").hide();
    $("#eebsmbuttonajouter").hide();
  }
  
})



$("#table_cmd").delegate(".listEquipementInfo", 'click', function () {
  var el = $(this)
  jeedom.cmd.getSelectModal({ cmd: { type: 'info' } }, function (result) {
    var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.data('input') + ']')
    calcul.atCaret('insert', result.human)
  })
})

$("#table_cmd").delegate(".listEquipementAction", 'click', function () {
  var el = $(this)
  var subtype = $(this).closest('.cmd').find('.cmdAttr[data-l1key=subType]').value()
  jeedom.cmd.getSelectModal({ cmd: { type: 'action', subType: subtype } }, function (result) {
    var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.attr('data-input') + ']')
    calcul.atCaret('insert', result.human);
  })
})

$("#table_cmd").sortable({ axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true })

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
            
                      
  
  if (!isset(_cmd)) {
    var _cmd = { configuration: {} }
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {}
  }
  
      var tr ="";
      if (_cmd.configuration.visible_cmd == "1"){
        tr = '<tr class="cmd"  data-cmd_id="' + init(_cmd.id) + '">'      
      }else{
        tr = '<tr style="display:none;" class="cmd" data-cmd_id="' + init(_cmd.id) + '">'      
      }
  
  		
     
        
      //ID  
  	  tr += '<td class="hidden-xs">'  
      tr += '<span  class="cmdAttr" data-l1key="id"></span>'
      tr += '</td>'
  	  
      //Nom
      tr += '<td>'
      tr += '<div class="input-group">'
      tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  	  tr += '<div style="display: none">'
      tr += _cmd.name;
      tr += '</div>'  	
      tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
      tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
      tr += '</div>'  
      tr += '</td>'
  
  	  //ID eeBSM
      tr += '<td class="num">'
  	  
        if (_cmd.configuration.commande != "-1"){
          
            /*if ((_cmd.type == 'info' && _cmd.configuration.type == 'info') || _cmd.type == 'action'){
                tr += '<div disabled class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="commande" placeholder="eebsm" title="{{Commande eebsm}}" style="display: none; width:50px;display:inline-block;margin-right:2px;"></div>'
            }else{
                tr += '<div style="display: none">'+_cmd.configuration.commande+'</div>'                    
            }*/
            	
          
        }
      
      tr += '</td>'
  
  	  //Unités
      tr += '<td>'
      if (_cmd.configuration.type == "info"){
        tr += '<div style="display: none">'
        tr += _cmd.unite;
        tr += '</div>'
  	  	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:60px; display:inline-block;margin-right:2px;">'        
      }
      tr += '</td>'
   
      //Afficher
      tr += '<td>'
  	  var value= "0";
  	  if (_cmd.isVisible == "1") value = "1";         
      tr += '<div sorttable_customkey='+value+' >' 
      tr += '<label   class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
   	  tr += '</div></td>'
  
      //Historiser
      tr += '<td>'
       if (_cmd.type == "info"){
         if (_cmd.isHistorized == "1") value = "1"; 
         
        tr += '<div sorttable_customkey='+value+' >'         
        
  		tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized"/>{{Historiser}}</label> '
        tr += '</div>'      
       }      
      tr += '</td>'
  
      //Type
  	  tr += '<td>'
  	  tr += '<span class="cmdAttr">';  		
      tr += '<div>'
      tr += _cmd.type;
  	  tr += ' ('
  	  tr += _cmd.configuration.type;
      tr += ')'
      tr += '</div>'        
  	  tr += '</span>'
  	  tr += '</td>'
  
      //Paramètres
  	  tr += '<td>'			
  	  if (_cmd.type == "info" && init(_cmd.logicalId) != 'Statut' ){
        tr += '<div style="display: none">'
        tr += _cmd.configuration.calcul;
        tr += '</div>'
    	tr += '<textarea class="cmdAttr  input-sm" data-l1key="configuration" data-l2key="calcul" style="height:35px;width:85%" placeholder="{{Recevoir de}}"></textarea>'
      	tr += '<a class="btn btn-default listEquipementInfo " data-input="calcul" style="margin-left:2px; height:35px; width:14%;"><i class="fas fa-list-alt"></i></a>'
      }
  	  if (_cmd.type == "action" && init(_cmd.logicalId) != 'Redémarrer' && init(_cmd.logicalId) != 'refresh'){
        tr += '<div style="display: none">'
        tr += _cmd.configuration.calcul;
        tr += '</div>'
    	tr += '<textarea class="cmdAttr  input-sm" data-l1key="configuration" data-l2key="calcul" style="height:35px;width:85%" placeholder="{{Envoyer vers}}"></textarea>'
      	tr += '<a class="btn btn-default listEquipementAction " data-input="calcul" style="margin-left:2px; height:35px; width:14%;"><i class="fas fa-list-alt"></i></a>'
      }
  		
  	  tr += '</td>'
  
      //Etat
      tr += '<td>';
      tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
      tr += '</td>';
  
      //Actions
      tr += '<td>'
  	  tr += '<div style="display: none">'
      tr += _cmd.type;
      tr += '</div>'
      if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
        //tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>'
      }
      tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
      tr += '</tr>'
     
      $('#table_cmd tbody').append(tr)
      var tr = $('#table_cmd tbody tr').last()
      jeedom.eqLogic.buildSelectCmd({
        id: $('.eqLogicAttr[data-l1key=id]').value(),
        filter: { type: 'info' },
        error: function (error) {
          $('#div_alert').showAlert({ message: error.message, level: 'danger' })
        },
        success: function (result) {
          tr.find('.cmdAttr[data-l1key=value]').append(result)
          tr.setValues(_cmd, '.cmdAttr')
          jeedom.cmd.changeType(tr, init(_cmd.subType))
        }
      })
  
}