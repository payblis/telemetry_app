<?php include 'includes/header.php'; ?>

<div class="content-container">
    <div class="page-header">
        <h2>Assistant IA - Télémétrie</h2>
    </div>
    
    <div class="row">
        <div class="col-33">
            <div class="card">
                <div class="card-header">
                    Paramètres de session
                </div>
                <div class="card-body">
                    <form action="index.php?page=ai_chat" method="post" id="sessionForm">
                        <div class="form-group">
                            <label for="session_id">Session (optionnel)</label>
                            <select name="session_id" id="session_id">
                                <option value="">Aucune session sélectionnée</option>
                                <?php foreach ($sessions as $session): ?>
                                <option value="<?php echo $session['id']; ?>" <?php echo (isset($_POST['session_id']) && $_POST['session_id'] == $session['id']) ? 'selected' : ''; ?>>
                                    <?php echo date('d/m/Y', strtotime($session['date'])) . ' - ' . $session['circuit_name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="moto_id">Moto</label>
                            <select name="moto_id" id="moto_id" required>
                                <option value="">Sélectionnez une moto</option>
                                <?php foreach ($motos as $moto): ?>
                                <option value="<?php echo $moto['id']; ?>" <?php echo (isset($_POST['moto_id']) && $_POST['moto_id'] == $moto['id']) ? 'selected' : ''; ?>>
                                    <?php echo $moto['brand'] . ' ' . $moto['model']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="circuit_id">Circuit</label>
                            <select name="circuit_id" id="circuit_id" required>
                                <option value="">Sélectionnez un circuit</option>
                                <?php foreach ($circuits as $circuit): ?>
                                <option value="<?php echo $circuit['id']; ?>" <?php echo (isset($_POST['circuit_id']) && $_POST['circuit_id'] == $circuit['id']) ? 'selected' : ''; ?>>
                                    <?php echo $circuit['name'] . ' (' . $circuit['country'] . ')'; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="pilot_id">Pilote</label>
                            <select name="pilot_id" id="pilot_id" required>
                                <option value="">Sélectionnez un pilote</option>
                                <?php foreach ($pilots as $pilot): ?>
                                <option value="<?php echo $pilot['id']; ?>" <?php echo (isset($_POST['pilot_id']) && $_POST['pilot_id'] == $pilot['id']) ? 'selected' : ''; ?>>
                                    <?php echo $pilot['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="weather">Conditions météo</label>
                            <select name="weather" id="weather" required>
                                <option value="Sec" <?php echo (isset($_POST['weather']) && $_POST['weather'] == 'Sec') ? 'selected' : ''; ?>>Sec</option>
                                <option value="Humide" <?php echo (isset($_POST['weather']) && $_POST['weather'] == 'Humide') ? 'selected' : ''; ?>>Humide</option>
                                <option value="Mouillé" <?php echo (isset($_POST['weather']) && $_POST['weather'] == 'Mouillé') ? 'selected' : ''; ?>>Mouillé</option>
                                <option value="Pluie légère" <?php echo (isset($_POST['weather']) && $_POST['weather'] == 'Pluie légère') ? 'selected' : ''; ?>>Pluie légère</option>
                                <option value="Pluie forte" <?php echo (isset($_POST['weather']) && $_POST['weather'] == 'Pluie forte') ? 'selected' : ''; ?>>Pluie forte</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="track_temperature">Température piste (°C)</label>
                            <input type="number" name="track_temperature" id="track_temperature" min="0" max="70" value="<?php echo isset($_POST['track_temperature']) ? $_POST['track_temperature'] : '25'; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="air_temperature">Température air (°C)</label>
                            <input type="number" name="air_temperature" id="air_temperature" min="0" max="50" value="<?php echo isset($_POST['air_temperature']) ? $_POST['air_temperature'] : '20'; ?>" required>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-67">
            <div class="card">
                <div class="card-header">
                    Conversation avec <?php echo $_SESSION['telemetrician_name']; ?>
                </div>
                <div class="card-body">
                    <div class="chat-container">
                        <div class="chat-messages" id="chatMessages">
                            <?php if (empty($messages)): ?>
                                <div class="chat-message system">
                                    <div class="message-content">
                                        <p>Bonjour, je suis <?php echo $_SESSION['telemetrician_name']; ?>, votre télémétriste virtuel. Comment puis-je vous aider aujourd'hui ?</p>
                                        <p>Vous pouvez me décrire un problème que vous rencontrez avec votre moto sur circuit, et je vous proposerai des réglages adaptés.</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <div class="chat-message <?php echo $message['is_user'] ? 'user' : 'ai'; ?>">
                                        <div class="message-content">
                                            <?php echo nl2br($message['content']); ?>
                                        </div>
                                        <div class="message-time">
                                            <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (isset($aiResponse)): ?>
                                    <div class="chat-message ai">
                                        <div class="message-content">
                                            <?php echo nl2br($aiResponse['solution']); ?>
                                        </div>
                                        <div class="message-time">
                                            <?php echo date('H:i'); ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (isset($aiResponse['feedback_id'])): ?>
                                    <div class="chat-message system">
                                        <div class="message-content">
                                            <p>Ces recommandations vous ont-elles été utiles ?</p>
                                            <div class="feedback-buttons">
                                                <a href="index.php?page=ai_validate&id=<?php echo $aiResponse['feedback_id']; ?>&type=POSITIVE" class="btn btn-success">Oui, très utiles</a>
                                                <a href="index.php?page=ai_validate&id=<?php echo $aiResponse['feedback_id']; ?>&type=NEUTRAL" class="btn btn-secondary">Partiellement utiles</a>
                                                <a href="index.php?page=ai_validate&id=<?php echo $aiResponse['feedback_id']; ?>&type=NEGATIVE" class="btn btn-danger">Non, pas utiles</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="chat-input">
                            <form action="index.php?page=ai_chat" method="post" id="chatForm">
                                <div class="form-group">
                                    <label for="problem_type">Type de problème</label>
                                    <select name="problem_type" id="problem_type" required>
                                        <option value="">Sélectionnez un type de problème</option>
                                        <option value="SUSPENSION_AVANT">Suspension avant</option>
                                        <option value="SUSPENSION_ARRIERE">Suspension arrière</option>
                                        <option value="ADHERENCE_AVANT">Adhérence avant</option>
                                        <option value="ADHERENCE_ARRIERE">Adhérence arrière</option>
                                        <option value="FREINAGE">Freinage</option>
                                        <option value="ACCELERATION">Accélération</option>
                                        <option value="STABILITE">Stabilité</option>
                                        <option value="COMPORTEMENT_GENERAL">Comportement général</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <textarea name="problem" id="problem" rows="3" placeholder="Décrivez votre problème en détail..." required></textarea>
                                </div>
                                
                                <div class="form-group text-right">
                                    <button type="button" class="btn btn-primary" id="sendButton">Envoyer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('sendButton').addEventListener('click', function() {
    // Vérifier si les champs du formulaire de session sont remplis
    var sessionForm = document.getElementById('sessionForm');
    var motoId = document.getElementById('moto_id').value;
    var circuitId = document.getElementById('circuit_id').value;
    var pilotId = document.getElementById('pilot_id').value;
    
    if (!motoId || !circuitId || !pilotId) {
        alert('Veuillez remplir tous les champs obligatoires dans les paramètres de session.');
        return;
    }
    
    // Vérifier si les champs du formulaire de chat sont remplis
    var problemType = document.getElementById('problem_type').value;
    var problem = document.getElementById('problem').value;
    
    if (!problemType || !problem) {
        alert('Veuillez sélectionner un type de problème et décrire votre problème.');
        return;
    }
    
    // Combiner les deux formulaires et soumettre
    var sessionFormData = new FormData(sessionForm);
    var chatFormData = new FormData(document.getElementById('chatForm'));
    
    // Créer un formulaire caché pour soumettre toutes les données
    var form = document.createElement('form');
    form.method = 'post';
    form.action = 'index.php?page=ai_chat';
    form.style.display = 'none';
    
    // Ajouter les données du formulaire de session
    for (var pair of sessionFormData.entries()) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = pair[0];
        input.value = pair[1];
        form.appendChild(input);
    }
    
    // Ajouter les données du formulaire de chat
    for (var pair of chatFormData.entries()) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = pair[0];
        input.value = pair[1];
        form.appendChild(input);
    }
    
    // Ajouter le formulaire au document et le soumettre
    document.body.appendChild(form);
    form.submit();
});

// Faire défiler la conversation jusqu'en bas
window.onload = function() {
    var chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
};
</script>

<?php include 'includes/footer.php'; ?>
