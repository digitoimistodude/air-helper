/**
 * Open the inserter on all patterns when a new, empty page is opened.
 *
 * @package air-helper
 */
( function ( wp ) {
  const { select, dispatch, subscribe } = wp.data;

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
      if ( 'auto-draft' !== post.status || ! editor.isEditedPostEmpty() ) {
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
