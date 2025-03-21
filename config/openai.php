<?php
define('OPENAI_API_KEY', 'cle-api-openai');

function getOpenAIClient() {
    return [
        'api_key' => OPENAI_API_KEY,
        'model' => 'gpt-4-turbo-preview',
        'temperature' => 0.7,
        'max_tokens' => 2000
    ];
}
?> 