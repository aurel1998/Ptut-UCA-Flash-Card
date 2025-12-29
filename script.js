// Fonction pour afficher/masquer les choix QCM et le champ verso
function toggleChoixQCM() {
    var typeSelect = document.getElementById('type');
    var choixSection = document.getElementById('choix-qcm');
    var versoGroup = document.getElementById('verso-group');
    var versoInput = document.getElementById('texte_verso');
    
    if (typeSelect.value === 'qcm') {
        choixSection.style.display = 'block';
        if (versoGroup) {
            versoGroup.style.display = 'none';
            if (versoInput) {
                versoInput.removeAttribute('required');
                versoInput.value = '';
            }
        }
    } else {
        choixSection.style.display = 'none';
        if (versoGroup) {
            versoGroup.style.display = 'block';
            if (versoInput) {
                versoInput.setAttribute('required', 'required');
            }
        }
    }
}

// Fonction pour ajouter un nouveau choix dans un QCM
function ajouterChoix() {
    var container = document.getElementById('choix-container');
    var choixItems = container.querySelectorAll('.choix-item');
    var nouveauIndex = choixItems.length;
    
    var nouveauChoix = document.createElement('div');
    nouveauChoix.className = 'choix-item';
    
    var input = document.createElement('input');
    input.type = 'text';
    input.name = 'choix[]';
    input.placeholder = 'Choix ' + (nouveauIndex + 1);
    
    var label = document.createElement('label');
    var checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.name = 'choix_correct[]';
    checkbox.value = nouveauIndex;
    label.appendChild(checkbox);
    label.appendChild(document.createTextNode(' Correct'));
    
    nouveauChoix.appendChild(input);
    nouveauChoix.appendChild(label);
    
    container.appendChild(nouveauChoix);
}

// Initialise l'affichage des choix QCM au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('type');
    if (typeSelect) {
        toggleChoixQCM();
    }
});

