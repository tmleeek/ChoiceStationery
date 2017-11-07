// create object
proposal = {

    tab: null,

    titleToHTML: function(id){

        return '<h1>'+proposal.tab[id]['title']+'</h1>';

    },

    contentToHTML: function(id){

        var retour = '';

        switch(proposal.tab[id]['mode']){
            case 'text':
                var reg = new RegExp("\n", 'g');
                var txt = proposal.tab[id]['content'];
                retour = '<p>'+txt.replace(reg,"<br/>")+'</p>';
                break;
            case 'list':
                var content = proposal.tab[id]['content'];
                var tmp = content.split("\n");
                retour += '<ul class="list">';
                for(var i = 0; i<tmp.length;i++){
                    retour += '<li>'+tmp[i]+'</li>';
                }
                retour += '</ul>';
                break;
        }

        return retour;

    },

    titleToEdit: function(id){

        return '<input id="edit_title_'+id+'" onBlur="proposal.updateField(event, this.value, \'title\', '+id+', this.parentNode);" type="text" value="'+proposal.tab[id]['title']+'" />';
    },

    contentToEdit: function(id){

        var retour = '<div class="proposal_content_container">';
        retour += '<div class="proposal_content_toolbar">';
        retour += '<div class="proposal_content_toolbar_left">';
        retour += proposal.modeToEdit(id);
        retour += '</div><div class="proposal_content_toolbar_right">';
        retour += '<button onClick="proposal.removeSection('+id+');return false;">Delete</button>';
        retour += '<button onClick="proposal.updateField(event, document.getElementById(\'edit_content_'+id+'\').value, \'content\', '+id+', document.getElementById(\'div_content_'+id+'\'));return false;" class="scalable save" type="button">Preview</button>';
        retour += '</div></div><div class="proposal_content_textarea">';
        retour += '<textarea id="edit_content_'+id+'" >'+proposal.tab[id]['content']+'</textarea></div></div>';

        //onBlur="proposal.updateField(event, document.getElementById(\'edit_content_'+id+'\').value, \'content\', '+id+', document.getElementById(\'div_content_'+id+'\'));return false;"

        return retour;

    },

    modeToEdit: function(id){

        var textChecked = (proposal.tab[id]['mode'] == 'text') ? 'selected' : '';
        var listChecked = (proposal.tab[id]['mode'] == 'list') ? 'selected' : '';

        var retour = '<label for="edit_mode_section_'+id+'">Display as </label> : <select name="edit_mode_section_'+id+'" id="edit_mode_section_'+id+'" onChange="proposal.updateField(event, this.value, \'mode\', '+id+', this.parentNode);">';
        retour += '<option value="text" '+textChecked+'>Text</option>';
        retour += '<option value="list" '+listChecked+'>Liste</option>';
        retour += '</select>';

        return retour;

    },

    switchTitleToEdit: function(id, elt){

        elt.removeChild(elt.firstChild);
        elt.setAttribute('onClick', '');
        elt.innerHTML = proposal.titleToEdit(id);
        document.getElementById('edit_title_'+id).focus();

        return false;

    },

    switchTitleToHTML: function(id, elt){

        elt.removeChild(elt.firstChild);
        elt.setAttribute('onClick', 'proposal.switchTitleToEdit('+id+', this)');
        elt.innerHTML = proposal.titleToHTML(id);

        return false;

    },

    switchContentToEdit: function(id, elt){

        elt.removeChild(elt.firstChild);
        elt.setAttribute('onClick', '');
        elt.innerHTML = proposal.contentToEdit(id);
        document.getElementById('edit_content_'+id).focus();

        return false;

    },

    switchContentToHTML: function(id, elt){

        while(elt.firstChild)
            elt.removeChild(elt.firstChild);

        elt.innerHTML = proposal.contentToHTML(id);
        elt.setAttribute('onClick', 'proposal.switchContentToEdit('+id+', this)');

        return false;

    },

    updateField: function(e, text, type, id, parent){

        if(!e)
            e = window.event;
        
        if(e.cancelBuddle)
            e.cancelBuddle = true;
        else
            e.stopPropagation();

        proposal.tab[id][type] = text;

        switch(type){
            case 'title':
                document.getElementById('title_section_'+id).value = text;
                proposal.switchTitleToHTML(id, parent);
                break;
            case 'content':
                document.getElementById('content_section_'+id).innerHTML = text;
                proposal.switchContentToHTML(id, parent);
                break;
            case 'mode':
                switch(text){
                    case 'text':
                        document.getElementById('mode_section_'+id+'_text').checked = true;
                        document.getElementById('mode_section_'+id+'_list').checked = false;
                        break;
                    case 'list':
                        document.getElementById('mode_section_'+id+'_text').checked = false;
                        document.getElementById('mode_section_'+id+'_list').checked = true;
                        break;
                }
                break;
        }


    },

    init: function(data){

        this.tab = data;
        var html = '';

        for(var j=0; j < proposal.tab.length; j++){

            html += '<div id="section_'+j+'" class="proposal_section">';
            html += '<span onClick="proposal.switchTitleToEdit('+j+', this)">'+proposal.titleToHTML(j)+'</span>';
            html += '<div class="proposal_html_content" id="div_content_'+j+'" onClick="proposal.switchContentToEdit('+j+', this)">'+proposal.contentToHTML(j)+'</div>';
            html += '</div>';

        }

        document.getElementById('preview').innerHTML = html;

        return false;

    },

    addSection: function(){

        // add part in preview, set default values
        // add new entry to proposal tab
        var i = this.tab.length;
        this.tab[i] = new Array;
        this.tab[i]['title'] = 'new title';
        this.tab[i]['content'] = 'new content';
        this.tab[i]['mode'] = 'text';

        // add part in hidden form
        this.addSectionToForm(i);

        var retour = '';
        retour = '<div id="section_'+i+'" class="proposal_section">';
        retour += '<span id="ancre_section_'+i+'" onClick="proposal.switchTitleToEdit('+i+', this)">'+this.titleToHTML(i)+'</span>';
        retour += '<div class="proposal_html_content" id="div_content_'+i+'" onClick="proposal.switchContentToEdit('+i+', this)">'+this.contentToHTML(i)+'</div>';
        retour += '</div>';

        document.getElementById('preview').innerHTML = document.getElementById('preview').innerHTML + retour;
        window.location.href = '#ancre_section_'+i;

        return false;

    },

    removeSection: function(id){

        var div = document.getElementById('section_'+id);
        div.style.display = 'none';

        document.getElementById('title_section_'+id).value = '';
        document.getElementById('content_section_'+id).innerHtml = '';

        return false;

    },

    addSectionToForm: function(nbr_section){

        var list = document.createElement('ul');

        var label_input = document.createElement('label');
        label_input.setAttribute('for', 'title_section_'+nbr_section);
        label_input.innerHTML = 'Titre';

        var li = document.createElement('li');
        li.appendChild(label_input);
        list.appendChild(li);

        var input = document.createElement('input');
        input.setAttribute('name', 'myform[proposal][title_section_'+nbr_section+']');
        input.setAttribute('id', 'title_section_'+nbr_section);
        input.setAttribute('value', 'new title');
        li = document.createElement('li');
        li.appendChild(input);
        list.appendChild(li);

        var label_textarea = document.createElement('label');
        label_textarea.setAttribute('for', 'content_section_'+nbr_section);
        label_textarea.innerHTML = 'Content';
        li = document.createElement('li');
        li.appendChild(label_textarea);
        list.appendChild(li);

        var textarea = document.createElement('textarea');
        textarea.setAttribute('name', 'myform[proposal][content_section_'+nbr_section+']');
        textarea.setAttribute('id', 'content_section_'+nbr_section);
        textarea.setAttribute('class', 'content_area');
        textarea.innerHTML = 'new content';
        list.appendChild(document.createElement('li').appendChild(textarea));
        li = document.createElement('li');
        li.appendChild(textarea);
        list.appendChild(li);

        var label_mode = document.createElement('label');
        label_mode.setAttribute('for', 'mode_section_'+nbr_section+'_text');
        label_mode.innerHTML = 'Text';

        var radio = document.createElement('input');
        radio.setAttribute('type', 'radio');
        radio.setAttribute('name', 'myform[proposal][mode_section_'+nbr_section+']');
        radio.setAttribute('id', 'mode_section_'+nbr_section+'_text');
        radio.setAttribute('value', 'text');
        radio.checked = true;
        li = document.createElement('li');
        li.appendChild(radio);
        li.appendChild(label_mode);
        list.appendChild(li);

        label_mode = document.createElement('label');
        label_mode.setAttribute('for', 'mode_section_'+nbr_section+'_list');
        label_mode.innerHTML = 'List';

        radio = document.createElement('input');
        radio.setAttribute('type', 'radio');
        radio.setAttribute('name', 'myform[proposal][mode_section_'+nbr_section+']');
        radio.setAttribute('id', 'mode_section_'+nbr_section+'_list');
        radio.setAttribute('value', 'list');

        li = document.createElement('li');
        li.appendChild(radio);
        li.appendChild(label_mode);
        list.appendChild(li);

        document.getElementById('proposal_hidden_form').appendChild(list);

    }
}
