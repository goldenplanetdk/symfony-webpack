function a() {
	require('@a/featureA.css');
	require('@golden_planet_webpack_test/asset.js');
	require('@root/app/Resources/assets/assetB-include.js');

	require('style.less');
	require('style.scss');
}
function b() {
	require.ensure([], function() {
		require('featureA.js');
		require('featureB.js');
	});
}
