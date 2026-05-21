$.noConflict();

jQuery(document).ready(function($) {

	"use strict";

	[].slice.call( document.querySelectorAll( 'select.cs-select' ) ).forEach( function(el) {
		new SelectFx(el);
	} );

	jQuery('.selectpicker').selectpicker;


	$('#menuToggle').on('click', function(event) {
		$('body').toggleClass('open');
	});

	$('.search-trigger').on('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		$('.search-trigger').parent('.header-left').addClass('open');
	});

	$('.search-close').on('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		$('.search-trigger').parent('.header-left').removeClass('open');
	});

	$('.user-area > a').on('click', function(event) {
		event.preventDefault();
		event.stopPropagation();

		var $container = $(this).closest('.user-area');
		var $menu = $container.find('.user-menu');

		$('.user-area').not($container).removeClass('open show');
		$('.user-menu').not($menu).removeClass('show');

		$container.toggleClass('open show');
		$menu.toggleClass('show');
	});

	$(document).on('click', function() {
		$('.user-area').removeClass('open show');
		$('.user-menu').removeClass('show');
	});

	$('.user-menu').on('click', function(event) {
		if (!$(event.target).closest('[data-toggle="modal"]').length) {
			event.stopPropagation();
		}
	});


});