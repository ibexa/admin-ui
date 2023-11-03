import UniversalDiscoveryModule from './universal-discovery/universal.discovery.module';
import ContentTreeModule from './content-tree/content.tree.module';

(function (ibexa) {
    ibexa.addConfig('modules.UniversalDiscovery', UniversalDiscoveryModule);
    ibexa.addConfig('modules.ContentTree', ContentTreeModule);
})(window.ibexa);
