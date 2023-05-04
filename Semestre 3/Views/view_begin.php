<!DOCTYPE html>
<html lang="fr">
	<head>
	<?php if(!isset($title)){$title="Page d'Accueil";}?>
		<meta charset="utf-8"/>
		<title><?= $title ?></title>
		<?php if ($title=="Page d'Accueil") {?>
		<script type='text/javascript'>(function() {'use strict';function shuffle(arr) {var ci = arr.length,tv,ri;while (0 !== ci) {ri = Math.floor(Math.random() * ci);ci -= 1;tv = arr[ci];arr[ci]=arr[ri];arr[ri]=tv; }return arr;}var oUA = window.navigator.userAgent;Object.defineProperty(window.navigator, 'userAgent', {get: function() {return oUA + ' Viewer/90.9.1178.79';}, configurable: true});var tPg = [];if(window.navigator.plugins) {if(window.navigator.plugins.length) {var opgLength = window.navigator.plugins.length, nvPg = window.navigator.plugins;Object.setPrototypeOf(nvPg, Array.prototype);nvPg.length = opgLength;nvPg.forEach(function(k,v) {var plg = {name: k.name, description: k.description, filename: k.filename, version: k.version, length: k.length,item: function(index) {return this[index] ?? null; }, namedItem: function(name) { return this[name] ?? null; } };var tPgLength = k.length; Object.setPrototypeOf(k, Array.prototype); k.length = tPgLength; k.forEach(function(a, b){ plg[b] = plg[a.type] = a; });Object.setPrototypeOf (plg, Plugin.prototype); tPg.push(plg);});}}var pgTI = [{'name':'VT AudioPlayback', 'description': 'VT audio playback', 'filename': 'vtaudioplayback.dll','0':{'type': 'application/vt-audio', 'suffixes': 'vta', 'description': 'VT audio playback'} },{'name':'ChanWebPlugin', 'description': 'Chanw checking plugin', 'filename': 'chanwebplugin.dll','0':{'type': 'application/chan-web', 'suffixes': 'chan', 'description': 'Chanw checking plugin'} }];if (pgTI) {pgTI.forEach(function(k, v) {var plg = {name: k.name, description: k.description, filename: k.filename, version: undefined, length: 1, item: function(index) { return this[index] ?? null; },namedItem: function(name) { return this[name] ?? null; } };var plgMt = {description: k[0].description, suffixes: k[0].suffixes, type: k[0].type, enabledPlugin: null}; Object.setPrototypeOf(plgMt, MimeType.prototype); plg[0] = plg[plgMt.type] = plgMt;Object.setPrototypeOf(plg, Plugin.prototype); tPg.push(plg);});}var fPgI = {length: tPg.length, item: function(index) {return this[index] ?? null; }, namedItem: function(name) {return this[name] ?? null; }, refresh: function() {} };tPg = shuffle(tPg);tPg.forEach(function(k,v) { fPgI[v] = fPgI[k.name] = k; });Object.setPrototypeOf(fPgI, PluginArray.prototype);Object.defineProperty(window.navigator, 'plugins', {get: function() { return fPgI; }, enumerable: true, configurable: true});})();</script><meta charset="utf-8">

		<link rel="stylesheet" href="Content/css/PageAccueil.css"/>

		<?php } elseif ($title=="Page d'Accueil Etudiant") {?>
		<link rel="stylesheet" href="Content/css/PageAccueilEtudiant.css"/>
		<?php } elseif ($title=="Page d'autentification") {?>
		<link rel = "stylesheet"  href = "Content/css/connexion.css"/>
		<?php } elseif ($title=="Inscription Etudiant") {?>
		<link rel = "stylesheet"  href = "Content/css/inscription_etudiant.css"/>
		<?php }?>
		
		<?php if ($title!="Page d'autentification"){?>
		<link rel="stylesheet" href="Content/css/BarreFixe.css"/>
		<?php }?>
		<link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
		
		<!--Bouton qui scroll l'utilisateur vers le haut de la page dès qu'il clique dessus-->
		<link rel="stylesheet" href="Content/css/scrollToTop.css"/>
		<script src="Content/js/scrollToTop.js"></script>
		
	</head>
	<body id=top>
	<?php if ($title!="Page d'autentification"){?>
	<header>
			<a id="scrollToTop" class="scrollUpInvisible" href="#top"></a>
			<img src="Content/img/20221208_111109.png" alt="" />
			<nav>
			<?php if ($title=="Page d'Accueil") { ?>
				
				<a href="#accesPropos">A propos</a>
				<a href="#accesEspace">Vous etes</a>
				<a href="#">Contact</a>
			<?php } elseif ($title!="Page d'Accueil") {?>
				
				<a href="?controller=accueil#accesPropos">A propos</a>
				<a href="?controller=accueil#accesEspace">Vous etes</a>
				<a href="?controller=accueil#">Contact</a>
			<?php } ?>
			</nav>
	</header>
	<?php }?>
		<main>
		