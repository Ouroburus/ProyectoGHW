<?php
/**
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang_Pro\Modules\Import_Export\Services\Context;

/**
 * PO file, generated from exporting Polylang translations.
 *
 * @phpstan-import-type translationEntryRef from PLL_Export_Data
 *
 * @since 2.7
 */
class PLL_PO_Export extends PLL_Export_File {
	/**
	 * Po object.
	 *
	 * @var PO
	 */
	private $po;

	/**
	 * Constructor.
	 * Creates a PO object.
	 *
	 * @since 2.7
	 * @since 3.6 Added `$source_language` and `$target_language` parameters.
	 *
	 * @param PLL_Language $source_language The source language of the exported data.
	 * @param PLL_Language $target_language The target language of the exported data.
	 */
	public function __construct( PLL_Language $source_language, PLL_Language $target_language ) {
		parent::__construct( $source_language, $target_language );
		require_once ABSPATH . '/wp-includes/pomo/po.php';
		$this->po = new PO();
	}

	/**
	 * Adds a source string to exported data and optionally a pre-existing translated one.
	 *
	 * @since 2.7
	 * @since 3.6 The first parameter is changed from `string $type` to `array $ref`.
	 *            Type-hinted.
	 *
	 * @param array  $ref    {
	 *     Array containing the content type and optionally the corresponding object ID.
	 *
	 *     @type string $object_type   Object type to be exported (e.g. `post` or `term`).
	 *     @type string $field_type    Field type to be exported (e.g. `post_content`, `post_title`...).
	 *     @type int    $object_id     A unique identifier to retrieve the corresponding object from the database.
	 *     @type string $field_id      Optional, a unique identifier to retrieve the corresponding field from the database.
	 *     @type string $field_comment Optional, a comment meant for the translators.
	 *     @type string $encoding      Optional, encoding format for the field group.
	 * }
	 * @param string $source The source to be translated.
	 * @param string $target Optional, a preexisting translation, if any.
	 * @return void
	 *
	 * @phpstan-param translationEntryRef $ref
	 * @phpstan-param non-empty-string $source
	 */
	public function add_translation_entry( array $ref, string $source, string $target = '' ) {
		if ( '' === $source ) {
			return;
		}

		$entry = new Translation_Entry(
			array(
				'singular'           => $source,
				'translations'       => array( $target ),
				'context'            => $ref['field_id'] ?? '',
				'extracted_comments' => $ref['field_comment'] ?? '',
			)
		);

		$this->po->add_entry( $entry );
	}

	/**
	 * Returns exported data.
	 *
	 * @since 3.1
	 *
	 * @return string
	 */
	public function get(): string {
		$this->po->set_comment_before_headers( $this->get_comment_before_headers() );

		$this->set_file_headers();

		return $this->po->export();
	}

	/**
	 * Assigns the necessary headers to the PO file.
	 *
	 * @see https://www.gnu.org/software/trans-coord/manual/gnun/html_node/PO-Header.html
	 *
	 * @since 2.7
	 * @since 3.3   Add a reference to the application that generated the export file (name + version).
	 * @since 3.3.1 Replace non-official "Language-Target" header to the official Language.
	 *              Use the Poedit header "X-Source-Language" instead of non official "Language-source".
	 *              Replace non official 'Site-Reference" header by "X-Polylang-Site-Reference".
	 * @since 3.6   Visibility is now private.
	 *
	 * @return void
	 */
	private function set_file_headers() {
		$this->po->set_header( 'Language', $this->get_target_language()->get_locale( 'display' ) );
		$this->po->set_header( 'Project-Id-Version', PLL_Import_Export::APP_NAME . '/' . POLYLANG_VERSION );
		$this->po->set_header( 'POT-Creation-Date', current_time( 'Y-m-d H:iO', true ) );
		$this->po->set_header( 'PO-Revision-Date', current_time( 'Y-m-d H:iO', true ) );
		$this->po->set_header( 'MIME-Version', '1.0' );
		$this->po->set_header( 'Content-Type', 'text/plain; charset=utf-8' );
		$this->po->set_header( 'Content-Transfer-Encoding', '8bit' );
		$this->po->set_header( 'X-Source-Language', $this->get_source_language()->get_locale( 'display' ) );
		$this->po->set_header( 'X-Polylang-Site-Reference', get_site_url() );
	}

	/**
	 *
	 * Get the necessary text comment to add to the PO file.
	 *
	 * @since 3.6 Visibility is now private.
	 *
	 * @return string
	 */
	private function get_comment_before_headers(): string {
		$po  = 'This file was generated by ' . POLYLANG . PHP_EOL;
		$po .= 'https://polylang.pro/' . PHP_EOL;
		return $po;
	}

	/**
	 * Returns the current file extension.
	 *
	 * @since 3.1
	 *
	 * @return string The file extension.
	 */
	protected function get_extension(): string {
		return 'po';
	}
}
