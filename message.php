<?php
require_once(__DIR__."/vendor/autoload.php");

use Orhanerday\OpenAi\OpenAi;
use League\CommonMark\CommonMarkConverter;

header( "Content-Type: application/json" );

$context = json_decode( $_POST['context'] ?? "[]" ) ?: [];


$open_ai_key_arr = array(
    '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',
    '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',
    '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'
    );
$open_ai_key = array_rand($open_ai_key_arr);

$random_key = $open_ai_key_arr[$open_ai_key];

$open_ai = new OpenAi($random_key);


$prompt = "This is the preface content that accompanies every submission, which can be left empty\n\n";


if( empty( $context ) ) {

    $prompt .= "
   Question:\n'My car won't start. What should i do?
    \n\nAnswer:\nMaybe start by getting it towed
    \n\nQuestion:\n'What is AI Garage?
    \n\nAnswer:\nAI Garage is an ai supported by chatgpt to help users around the world with their car's issues. I am your AI Mechanic who can help guide you throughout the way!
    ";
    
    $please_use_above = "";
} else {
    

    $prompt .= "";
    $context = array_slice( $context, -5 );
    foreach( $context as $message ) {
        $prompt .= "Question:\n" . $message[0] . "\n\nAnswer:\n" . $message[1] . "\n\n";
    }
    $please_use_above = ". Please use the questions and answers above as context for the answer.";
}

// add new question to prompt
$prompt = $prompt . "Question:\n" . $_POST['message'] . $please_use_above . "\n\nAnswer:\n\n";

// create a new completion
$complete = json_decode( $open_ai->completion( [
    'model' => 'text-davinci-003',
    'prompt' => $prompt,
    'temperature' => 0.9,
    'max_tokens' => 2000, 
    'top_p' => 1,
    'frequency_penalty' => 0,
    'presence_penalty' => 0,
    'stop' => [
        "\nNote:",
        "\nQuestion:"
    ]
] ) );

// get message text
if( isset( $complete->choices[0]->text ) ) {
    $text = str_replace( "\\n", "\n", $complete->choices[0]->text );
} elseif( isset( $complete->error->message ) ) {
    $text = $complete->error->message;
} else {
    $text = "Sorry, but I don't know how to answer that.";
}


$converter = new CommonMarkConverter();
$styled = $converter->convert( $text );

// return response
echo json_encode( [
    "message" => (string)$styled,
    "raw_message" => $text,
    "status" => "success",
] );
