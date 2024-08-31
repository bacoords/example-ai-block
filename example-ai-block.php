<?php
/**
 * Plugin Name:       Example AI Block
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       example-ai-block
 *
 * @package CreateBlock
 */

namespace WPDev\Ai_Block;

use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_ai_block_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', __NAMESPACE__ . '\create_block_ai_block_block_init' );


/**
 * Register our REST endpoint.
 *
 * @return void
 */
function register_our_rest_endpoint() {
	register_rest_route(
		'example-ai-block/v1',
		'/get-data',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\get_data',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\register_our_rest_endpoint' );



/**
 * Get data from OpenAI.
 *
 * @param array $request The request data.
 * @return WP_REST_Response
 */
function get_data( $request ) {

	if ( ! defined( 'OPENAI_KEY' ) ) {
		return new WP_REST_Response(
			array(
				'error' => 'No OpenAI key defined',
			),
			400
		);
	}
	$content   = $request['content'] ?? '';
	$title     = $request['title'] ?? '';
	$permalink = $request['permalink'] ?? '';

	$api_key = OPENAI_KEY;

	// Strip all HTML tags.
	$content = wp_strip_all_tags( $content );

	$prompt = "Read the article and generate a tweet with one interesting quote from the article and includes the link: $permalink. The article is named: '$title'. Here is the content: $content";

	$response = wp_remote_post(
		'https://api.openai.com/v1/chat/completions',
		array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'model'    => 'gpt-4o-mini',
					// 'max_tokens' => 150,
					'messages' => array(
						array(
							'role'    => 'system',
							'content' => 'You are a social media manager for a large company. You have been asked to generate a tweet based on the following article:',
						),
						array(
							'role'    => 'user',
							'content' => $prompt,
						),
					),
				)
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_REST_Response(
			array(
				'error' => 'Error connecting to OpenAI',
				'data'  => $response,
			),
			500
		);
	}

	$body = wp_remote_retrieve_body( $response );

	$data = json_decode( $body, true );

	if ( ! $data ) {
		return new WP_REST_Response(
			array(
				'error' => 'Error decoding OpenAI response',
				'data'  => $body,
			),
			500
		);
	}

	$choices = $data['choices'] ?? array();

	if ( empty( $choices ) ) {
		return new WP_REST_Response(
			array(
				'error' => 'No choices in OpenAI response',
				'data'  => $data,
			),
			500
		);
	}

	$choice = $choices[0] ?? array();

	if ( empty( $choice ) ) {
		return new WP_REST_Response(
			array(
				'error' => 'No choice in OpenAI response',
				'data'  => $data,
			),
			500
		);
	}

	$text = $choice['message']['content'] ?? '';

	if ( empty( $text ) ) {
		return new WP_REST_Response(
			array(
				'error' => 'No text in OpenAI response',
				'data'  => $data,
			),
			500
		);
	}

	// Remove the prompt from the response.
	$text = str_replace( $prompt, '', $text );

	// Remove the "Tweet:" prefix.
	$text = str_replace( 'Tweet:', '', $text );

	// Remove any leading or trailing whitespace.
	$text = trim( $text );

	return new WP_REST_Response(
		array(
			'tweet' => $text,
		),
		200
	);
}
