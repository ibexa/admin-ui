import getIbexaConfig from '@ibexa/eslint-config/eslint';

export default [
    {
        ignores: ['**/*', '!src', '!src/bundle/**'],
    },
    ...getIbexaConfig(),
];
