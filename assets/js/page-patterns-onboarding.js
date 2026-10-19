/**
 * Open the inserter on all patterns when a new, empty page is opened,
 * and on the Patterns tab when "+" is pressed on an existing page.
 *
 * @package air-helper
 */
( function ( wp ) {
  const { select, dispatch, subscribe } = wp.data;

  // With the template shown, core aims the inserter at the post content block and our options would skip that
  function isTemplateLocked() {
    return 'template-locked' === select( 'core/editor' ).getRenderingMode();
  }

  // Core opens the toolbar "+" on the Blocks tab and has no public option for it
  document.addEventListener( 'click', function ( event ) {
    if ( ! event.target.closest( '.editor-document-tools__inserter-toggle' ) || select( 'core/editor' ).isInserterOpened() || isTemplateLocked() ) {
      return;
    }

    event.stopPropagation();
    dispatch( 'core/editor' ).setIsInserterOpened( { tab: 'patterns' } );
  }, true );

  wp.domReady( function () {
    const unsubscribe = subscribe( function () {
      const editor = select( 'core/editor' );
      const post = editor.getCurrentPost();

      // Wait until the post has loaded
      if ( ! post || ! post.status ) {
        return;
      }

      unsubscribe();

      // Only a page that has never been saved, with nothing typed into it yet
      if ( 'auto-draft' !== post.status || ! editor.isEditedPostEmpty() || isTemplateLocked() ) {
        return;
      }

      dispatch( 'core/editor' ).setIsInserterOpened( {
        tab: 'patterns',
        // Same name and translated label core uses for its own "All" pattern category
        category: { name: 'allPatterns', label: wp.i18n._x( 'All', 'patterns' ) },
      } );
    } );
  } );
} )( window.wp );
