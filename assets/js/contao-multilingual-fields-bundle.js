require('../scss/contao-multilingual-fields-bundle.be.scss');

function moveEditButton() {
    let widget = document.getElementById('mf_language_edit_switch_button_widget');
    let element = widget.getElementsByTagName('a')[0];
    let buttons = document.getElementById('tl_buttons');
    let elemParent = widget.parentElement;

    if (element && buttons) {
        buttons.appendChild(element);
        if (elemParent) {
            elemParent.remove();
        }
    }
}

document.addEventListener('DOMContentLoaded', moveEditButton);