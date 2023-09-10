<?php

namespace FileTemplates;

use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Title\Title;
use ConfigFactory;
use Html;

class Hooks implements ParserFirstCallInitHook {
    private $allowedMimeTypes;

    public function __construct( ConfigFactory $configFactory ) {
        $config = $configFactory->makeConfig( 'filetemplates' );
        $this->allowedMimeTypes = $config->get( 'FileTemplatesAllowedMimeTypes' );
    }

    public function onParserFirstCallInit( $parser ) {
        $parser->setFunctionHook( 'filetemplate', function ( $parser, $name = '' ) {
            $getErrorMessage = function ( $message ) use ( $name ) {
                return Html::element( 'div',
                                      [ 'class' => 'error' ],
                                      wfMessage( 'filetemplates-function-error', $name, $message )->text()
                                     );
            };

            if ( empty( $this->allowedMimeTypes ) ) {
                return $getErrorMessage( wfMessage( 'filetemplates-no-mime-types-error' ) );
            }

            $title = Title::makeTitleSafe( NS_FILE, $name );
            if ( !$title ) {
                return $getErrorMessage( wfMessage( 'filetemplates-invalid-file-name-error' ) );
            }

            if ( !$title->exists() ) {
                return $getErrorMessage( wfMessage( 'filetemplates-file-not-found-error', $title ) );
            }

            $file = $parser->fetchFileAndTitle( $title )[0];
            if ( !$file ) {
                return $getErrorMessage( wfMessage( 'filetemplates-fetch-file-failed-error' ) );
            }

            if ( !in_array( $file->getMimeType(), $this->allowedMimeTypes ) ) {
                return $getErrorMessage( wfMessage( 'filetemplates-disallowed-mime-type-error',
                                                    $file->getMimeType(),
                                                    implode( ', ', $this->allowedMimeTypes)
                                                   ) );
            }

            $output = $file ? file_get_contents( $file->getLocalRefPath() ) : false;
            if ( !$output ) {
                return $getErrorMessage( wfMessage( 'filetemplates-get-contents-failed-error' ) );
            }

            return $parser->insertStripItem( str_replace( "\n", ' ', $output ) );
        });
    }
}
