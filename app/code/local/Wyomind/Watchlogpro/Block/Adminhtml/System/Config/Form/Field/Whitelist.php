<?php

class Wyomind_Watchlogpro_Block_Adminhtml_System_Config_Form_Field_Whitelist extends Mage_Adminhtml_Block_System_Config_Form_Field {


    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

        $html = "";

        $html .= "<input class=' input-text'  type='hidden' id='" . $element->getHtmlId() . "' name='" . $element->getName() . "' value='" . $element->getEscapedValue() . "' '" . $element->serialize($element->getHtmlAttributes()) . "/>";

        $html .= '<div class="grid" style="width:275px">
                    <div class="hor-scroll">
                        <table cellspacing="0" id="watchlogGrid_table" class="data">
                            <thead>
                                <tr class="headings">
                                    <th class="a-center" width="225px">IP</th>
                                    <th class="a-center" style="width:50px"><span class="nobr"></span></th>
                                </tr>
                            </thead>
                            <tbody id="wl_body">
                                ';
        $ips = json_decode($element->getValue());
 if (!is_array($ips))
            $ips = array();
        foreach ($ips as $ip) {
            $html .= '<tr title="#" class="pointer">'
                    . '<td class="a-center wl_ip">' . $ip . '</td>'
                    . '<td>'
                    . '<button class="scalable delete" type="button" onclick="wl_ip_delete(this);">
                        <span>
                            <span>
                                <span>Delete</span>
                            </span>
                        </span>
                       </button>'
                    . '</td>'
                    . '</tr>';
        }

        $html .= '<tr id="wl_add">
                    <td></td>
                    <td>
                        <button class="scalable add" type="button" onclick="wl_ip_add()" style="">
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
            var wl_ips = ' . ($element->getValue() != "" ? $element->getValue() : "[]") . ';
            function wl_ip_delete(button) {
                button.up("tr").remove();
                wl_update_ips();
            }
            function wl_ip_add() {
                var tr = document.createElement("tr");
                var td_ip = document.createElement("td");
                var td_button = document.createElement("td");
                var input = document.createElement("input");

                input.setAttribute("type","text");
                input.setAttribute("class","input-text");
                input.style.width="auto";
                input.observe("change",wl_update_ips);  
                
                td_ip.setAttribute("style","text-align:center");
                
                td_ip.appendChild(input);
                td_button.innerHTML = "<button style=\'width:100%\' class=\'scalable delete\' type=\'button\' onclick=\'wl_ip_delete(this);\'><span><span><span>Delete</span></span></span></button>";

                tr.appendChild(td_ip);
                tr.appendChild(td_button);
                $("wl_add").insert({before:tr});
                
            }
            function wl_update_ips() {
                var ips = new Array();
                $$(".wl_ip").each(function(ip) {
                    ips.push(ip.innerHTML);
                });
                $$("#wl_body .input-text").each(function(ip) {
                    ips.push(ip.value);
                });
                wl_ips = ips;
                console.log(wl_ips);
                $("' . $element->getHtmlId() . '").value = Object.toJSON(wl_ips);
            }
            ';
        $html .= '</script>';

        return $html;
    }

}
