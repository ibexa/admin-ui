// [TMP] Smoke test for frontend-ci.yaml under Node 22. Drop before merging.
import getIbexaConfig from '@ibexa/eslint-config/eslint';

export default [
    ...getIbexaConfig(),
];
