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
                                    <?php echo date('d/m/Y', strtotime($session['date'])) . ' - ' . htmlspecialchars($session['circuit_name']); ?>
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
                                    <?php echo htmlspecialchars($pilot['name']); ?>
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
                                    <?php echo htmlspecialchars($moto['brand'] . ' ' . $moto['model']); ?>
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
                                    <?php echo htmlspecialchars($circuit['name']); ?>
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
                            <input type="number" class="form-control" id="track_temperature" name="track_temperature" step="0.1" min="-20" max="60" value="<?php echo isset($_POST['track_temperature']) ? htmlspecialchars($_POST['track_temperature']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="air_temperature">Température air (°C)</label>
                            <input type="number" class="form-control" id="air_temperature" name="air_temperature" step="0.1" min="-20" max="60" value="<?php echo isset($_POST['air_temperature']) ? htmlspecialchars($_POST['air_temperature']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="problem_type">Type de problème</label>
                            <select name="problem_type" id="problem_type" required>
                                <option value="">Sélectionnez le type de problème</option>
                                <option value="STABILITY" <?php echo (isset($_POST['problem_type']) && $_POST['problem_type'] == 'STABILITY') ? 'selected' : ''; ?>>Stabilité</option>
                                <option value="TRACTION" <?php echo (isset($_POST['problem_type']) && $_POST['problem_type'] == 'TRACTION') ? 'selected' : ''; ?>>Adhérence</option>
                                <option value="BRAKING" <?php echo (isset($_POST['problem_type']) && $_POST['problem_type'] == 'BRAKING') ? 'selected' : ''; ?>>Freinage</option>
                                <option value="ACCELERATION" <?php echo (isset($_POST['problem_type']) && $_POST['problem_type'] == 'ACCELERATION') ? 'selected' : ''; ?>>Accélération</option>
                                <option value="HANDLING" <?php echo (isset($_POST['problem_type']) && $_POST['problem_type'] == 'HANDLING') ? 'selected' : ''; ?>>Tenue de route</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="problem">Description du problème</label>
                            <textarea class="form-control" id="problem" name="problem" rows="4" required><?php echo isset($_POST['problem']) ? htmlspecialchars($_POST['problem']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Obtenir des recommandations</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-66">
            <div class="card">
                <div class="card-header">
                    Réponses de l'assistant IA
                </div>
                <div class="card-body">
                    <?php if (empty($messages)): ?>
                        <p>Posez votre question pour obtenir des recommandations personnalisées.</p>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                        <div class="message <?php echo $message['is_user'] ? 'user-message' : 'ai-message'; ?>">
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($message['content'])); ?>
                            </div>
                            <div class="message-time">
                                <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
