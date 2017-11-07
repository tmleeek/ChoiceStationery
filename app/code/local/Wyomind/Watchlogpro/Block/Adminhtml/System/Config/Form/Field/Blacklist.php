<?php

class Wyomind_Watchlogpro_Block_Adminhtml_System_Config_Form_Field_Blacklist extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

        $html = "";

        $html .= "<input class=' input-text'  type='hidden' id='" . $element->getHtmlId() . "' name='" . $element->getName() . "' value='" . $element->getEscapedValue() . "' '" . $element->serialize($element->getHtmlAttributes()) . "/>";

        $html .= '<div class="grid" style="width:500px">
                    <div class="hor-scroll">
                        <table cellspacing="0" id="watchlogGrid_table" class="data">
                            <thead>
                                <tr class="headings">
                                    <th class="a-center" width="225px">IP</th>
                                    <th class="a-center" width="225px">Blocked until</th>
                                    <th class="a-center" style="width:50px"><span class="nobr"></span></th>
                                </tr>
                            </thead>
                            <tbody id="bl_body">
                                ';
        $ips = json_decode($element->getValue());
        if (!is_array($ips))
            $ips = array();
        foreach ($ips as $ip) {
            $html .= '<tr title="#" class="pointer">'
                    . '<td class="a-center bl_ip">' . $ip->ip . '</td>'
                    . '<td class="a-center">' . (!isset($ip->until) ? '-' : Mage::getModel('core/date')->date('m/d/Y H:i:s', strtotime($ip->until))) . '</td>'
                    . '<td>'
                    . '<button class="scalable delete" type="button" onclick="bl_ip_delete(this);">
                        <span>
                            <span>
                                <span>Delete</span>
                            </span>
                        </span>
                       </button>'
                    . '</td>'
                    . '</tr>';
        }

        $html .= '<tr id="bl_add">
                    <td colspan="2"></td>
                    <td>
                    <button class="scalable add" type="button" onclick="bl_ip_add()" style="">
                    <span>
                        <span>
                            <span>Add IP</span>
                        </span>
                    </span>
                   </button>
                   </td>
                   </tr>
                </tbody>
            </table>
            </div>
            </div>';

        $html .= '<script>';
        $html .= '
            var bl_ips = ' . ($element->getValue() != "" ? $element->getValue() : "[]") . ';
            function bl_ip_delete(button) {
                button.up("tr").remove();
                bl_update_ips();
            }
            function bl_ip_add() {
                var tr = document.createElement("tr");
                var td_ip = document.createElement("td");
                var td_button = document.createElement("td");
                var td_blocked_until = document.createElement("td");
                var input = document.createElement("input");

                input.setAttribute("type","text");
                input.setAttribute("class","input-text");
                input.style.width="auto";
                input.observe("change",bl_update_ips);  
                
                td_blocked_until.innerHTML = "-";
                td_blocked_until.setAttribute("class","a-center");
                td_ip.setAttribute("style","text-align:center");
                
                td_ip.appendChild(input);
                td_button.innerHTML = "<button style=\'width:100%\' class=\'scalable delete\' type=\'button\' onclick=\'bl_ip_delete(this);\'><span><span><span>Delete</span></span></span></button>";

                tr.appendChild(td_ip);
                tr.appendChild(td_blocked_until);
                tr.appendChild(td_button);
                $("bl_add").insert({before:tr});
                
            }
            function bl_update_ips() {
                var ips = new Array();
                $$(".bl_ip").each(function(ip) {
                    ips.push({ip:ip.innerHTML});
                });
                $$("#bl_body .input-text").each(function(ip) {
                    ips.push({ip:ip.value});
                });
                bl_ips = ips;
                console.log(bl_ips);
                $("' . $element->getHtmlId() . '").value = Object.toJSON(bl_ips);
            }
            ';
        $html .= '</script>';

        return $html;
    }

}
