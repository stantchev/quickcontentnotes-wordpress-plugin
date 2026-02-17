/**
 * Quick Content Notes v1.5 – Admin JavaScript
 * Author: Milen Stanchev (https://stanchev.bg/)
 */

/* global qcnVars, jQuery */
( function ( $, cfg ) {
    'use strict';

    // ─── Helpers ──────────────────────────────────────────────────────────────

    function ajax( action, data, done, fail ) {
        $.post( cfg.ajaxUrl, $.extend( { action: action, nonce: cfg.nonce }, data ) )
            .done( function ( r ) {
                if ( r.success ) { done && done( r.data ); }
                else { showNotice( ( r.data && r.data.message ) || cfg.i18n.error, 'error' ); fail && fail( r ); }
            } )
            .fail( function () { showNotice( cfg.i18n.error, 'error' ); fail && fail(); } );
    }

    function showNotice( msg, type ) {
        var cls = type === 'error' ? 'notice-error' : 'notice-success';
        var $n  = $( '<div class="notice ' + cls + ' is-dismissible" style="position:fixed;top:32px;right:16px;z-index:99999;min-width:220px;"><p>' + msg + '</p></div>' );
        $( 'body' ).append( $n );
        setTimeout( function () { $n.fadeOut( 400, function () { $n.remove(); } ); }, 3000 );
    }

    // ─── 1. Quick status toggle (posts list + admin dashboard) ───────────────

    $( document ).on( 'click', '.qcn-stat-btn', function (e) {
        e.preventDefault();
        var $btn    = $( this );
        var $wrap   = $btn.closest( '.qcn-quick-status' );
        var postId  = $wrap.data( 'post-id' );
        var status  = $btn.data( 'status' );

        $wrap.find( '.qcn-stat-btn' ).removeClass( 'qcn-btn-active' );
        $btn.addClass( 'qcn-btn-active' );

        ajax( 'qcn_update_status', { post_id: postId, status: status }, function () {
            showNotice( cfg.i18n.saved, 'success' );
            $wrap.data( 'current', status );
            var $label = $wrap.find( '.qcn-status-label' );
            if ( $label.length ) {
                var emojiMap = { active: '📌', 'in-progress': '🔄', completed: '✅' };
                var nameMap  = { active: cfg.i18n.active || 'Active', 'in-progress': 'In Progress', completed: 'Completed' };
                $label.text( emojiMap[ status ] + ' ' + nameMap[ status ] );
            }
            // If just completed, grey the row
            var $row = $wrap.closest( '.qcn-row' );
            if ( $row.length ) {
                $row.find( '.qcn-note-text' ).toggleClass( 'qcn-status-completed', status === 'completed' );
            }
        } );
    } );

    // ─── 2. Admin-bar status buttons ─────────────────────────────────────────

    $( document ).on( 'click', '.qcn-ab-status-btn a', function (e) {
        e.preventDefault();
        var $a     = $( this );
        var $li    = $a.closest( 'li' );
        var postId = $li.data( 'post-id' ) || $li.attr( 'data-post-id' );
        var status = $li.data( 'status' )  || $li.attr( 'data-status' );

        // Read from node's data attrs set in PHP via meta
        if ( ! postId ) {
            // Try parsing from the rendered adminbar li ID: qcn-set-status-{slug}
            var idAttr = $li.attr( 'id' ) || '';
            var match  = idAttr.match( /qcn-set-status-(.+)/ );
            if ( match ) status = match[1];
            // post ID must come from the page
            postId = $( 'input[name="post_ID"]' ).val() || new URLSearchParams( window.location.search ).get( 'post' );
        }

        if ( ! postId || ! status ) return;

        ajax( 'qcn_update_status', { post_id: postId, status: status }, function () {
            showNotice( cfg.i18n.saved, 'success' );
            // update active indicator
            $( '#wpadminbar .qcn-ab-status-btn' ).removeClass( 'qcn-ab-current-status' );
            $li.addClass( 'qcn-ab-current-status' );
        } );
    } );

    // ─── 3. History modal ─────────────────────────────────────────────────────

    var $modal          = $( '#qcn-history-modal' );
    var $modalContent   = $( '#qcn-history-content' );

    $( document ).on( 'click', '.qcn-history-btn', function (e) {
        e.preventDefault();
        var postId = $( this ).data( 'post-id' );
        $modal.show();
        $modalContent.html( '<p>' + ( cfg.i18n.saving || 'Loading…' ) + '</p>' );

        ajax( 'qcn_get_history', { post_id: postId }, function ( data ) {
            $modalContent.html( data.html );
        } );
    } );

    $( document ).on( 'click', '.qcn-modal-close, .qcn-modal-overlay', function () {
        $modal.hide();
    } );

    $( document ).on( 'keydown', function (e) {
        if ( e.key === 'Escape' ) $modal.hide();
    } );

    // ─── 4. Delete note ───────────────────────────────────────────────────────

    $( document ).on( 'click', '.qcn-delete-note', function (e) {
        e.preventDefault();
        if ( ! window.confirm( cfg.i18n.confirm_delete ) ) return;
        var $btn   = $( this );
        var postId = $btn.data( 'post-id' );

        ajax( 'qcn_delete_note', { post_id: postId }, function () {
            var $row = $btn.closest( 'tr, .qcn-row' );
            $row.fadeOut( 300, function () { $row.remove(); } );
            showNotice( cfg.i18n.saved, 'success' );
        } );
    } );

    // ─── 5. Meta box: template picker ────────────────────────────────────────

    $( document ).on( 'change', '#qcn_template_picker', function () {
        var content = $( this ).val();
        var color   = $( this ).find( ':selected' ).data( 'color' );
        var tplId   = $( this ).find( ':selected' ).data( 'id' );

        if ( ! content ) return;

        if ( $( '#qcn_note_content' ).val().trim() ) {
            if ( ! window.confirm( 'Replace current note with this template?' ) ) {
                $( this ).val( '' );
                return;
            }
        }

        $( '#qcn_note_content' ).val( content ).trigger( 'input' );
        if ( color ) $( '#qcn_note_color' ).val( color ).trigger( 'change' );
        if ( tplId ) $( '#qcn_template_used' ).val( tplId );

        $( this ).val( '' ); // reset picker
    } );

    // ─── 6. Meta box: colour → textarea border feedback ──────────────────────

    $( document ).on( 'change input', '#qcn_note_color', function () {
        var map = { default: '#2271b1', red: '#dc3232', yellow: '#dba617', green: '#28a745', blue: '#007bff' };
        $( '#qcn_note_content' ).css( 'border-left', '3px solid ' + ( map[ $( this ).val() ] || '#8c8f94' ) );
    } );
    $( '#qcn_note_color' ).trigger( 'change' );

    // ─── 7. Meta box: status → textarea opacity ───────────────────────────────

    $( document ).on( 'change', '#qcn_note_status', function () {
        var v = $( this ).val();
        $( '#qcn_note_content' )
            .css( 'text-decoration', v === 'completed' ? 'line-through' : '' )
            .css( 'opacity', v === 'completed' ? '.5' : '' );
    } );
    $( '#qcn_note_status' ).trigger( 'change' );

    // ─── 8. Meta box: history toggle ─────────────────────────────────────────

    $( document ).on( 'click', '.qcn-history-btn[data-panel]', function (e) {
        // Inline panel variant (meta box sidebar)
        e.preventDefault();
        var $btn   = $( this );
        var $panel = $btn.next( '.qcn-history-panel' );
        var postId = $btn.data( 'post-id' );

        if ( $panel.is( ':visible' ) ) {
            $panel.slideUp( 200 );
            return;
        }

        $panel.html( '<p style="font-size:12px;color:#646970;">Loading…</p>' ).slideDown( 200 );
        ajax( 'qcn_get_history', { post_id: postId }, function ( data ) {
            $panel.html( data.html );
        } );
    } );

    // History button inside meta box (no data-panel, uses modal)
    $( document ).on( 'click', '.qcn-history-btn:not([data-panel])', function (e) {
        // already handled above via the modal handler – no double binding needed.
    } );

    // ─── 9. Settings page: add/remove templates ───────────────────────────────

    var tplIndex = $( '.qcn-tpl-row' ).length;

    $( '#qcn-add-template' ).on( 'click', function () {
        var proto = $( '#qcn-tpl-prototype' ).html();
        if ( ! proto ) return;
        var html = proto.replace( /__INDEX__/g, tplIndex++ );
        $( '#qcn-templates-list' ).append( html );
    } );

    $( document ).on( 'click', '.qcn-remove-tpl', function () {
        $( this ).closest( '.qcn-tpl-row' ).fadeOut( 200, function () { $( this ).remove(); } );
    } );

    // ─── 10. Dismiss notices ─────────────────────────────────────────────────

    $( document ).on( 'click', '.notice.is-dismissible .notice-dismiss', function () {
        $( this ).closest( '.notice' ).fadeOut( 200 );
    } );

} )( jQuery, window.qcnVars || {} );
