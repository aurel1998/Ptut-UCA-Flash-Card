<?php
$titre_page = 'Inscription';
?>
<div class="container-auth">
    <div class="form-container">
        <h1>Inscription</h1>
        <?php if (isset($erreur) && $erreur): ?>
            <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>
        <?php if (isset($succes) && $succes): ?>
            <div class="message-succes"><?php echo htmlspecialchars($succes); ?></div>
        <?php endif; ?>
        <form method="POST" action="/index.php?controller=auth&action=register">
            <div class="form-group">
                <label for="nom">Nom complet</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirmation_mot_de_passe">Confirmer le mot de passe</label>
                <input type="password" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe" required minlength="6">
            </div>
            <div class="form-group">
                <label for="role">Rôle</label>
                <select id="role" name="role" required onchange="toggleFiliereAnnee()">
                    <option value="etudiant">Étudiant</option>
                    <option value="enseignant">Enseignant</option>
                </select>
            </div>
            <div class="form-group" id="filiere-group">
                <label for="filiere">Filière (optionnel)</label>
                <select id="filiere" name="filiere">
                    <option value="">-- Sélectionner --</option>
                    <option value="VCOD">VCOD</option>
                    <option value="EMS">EMS</option>
                    <option value="BIOSTAT">BIOSTAT</option>
                    <option value="GEA">GEA</option>
                </select>
            </div>
            <div class="form-group" id="annee-group">
                <label for="annee">Année (optionnel)</label>
                <select id="annee" name="annee">
                    <option value="">-- Sélectionner --</option>
                    <option value="1ère année">1ère année</option>
                    <option value="2ème année">2ème année</option>
                    <option value="3ème année">3ème année</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">S'inscrire</button>
        </form>
        <p class="lien-inscription">Déjà un compte ? <a href="/index.php?controller=auth&action=login">Se connecter</a></p>
        <p class="lien-inscription"><a href="/index.php?controller=accueil&action=index">← Retour à l'accueil</a></p>
    </div>
    <script>
        function toggleFiliereAnnee() {
            var role = document.getElementById('role').value;
            var filiereGroup = document.getElementById('filiere-group');
            var anneeGroup = document.getElementById('annee-group');
            
            if (role === 'enseignant') {
                filiereGroup.style.display = 'none';
                anneeGroup.style.display = 'none';
                document.getElementById('filiere').value = '';
                document.getElementById('annee').value = '';
            } else {
                filiereGroup.style.display = 'block';
                anneeGroup.style.display = 'block';
            }
        }
    </script>
</div>

