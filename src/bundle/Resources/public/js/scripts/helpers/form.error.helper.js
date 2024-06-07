import { formatErrorLine } from './form.validation.helper';

// @deprecated, will be removed in 5.0
const formatLine = (...args) => {
    console.warn(
        'helpers.formError.formatLine method is deprecated and will be removed in 5.0, please use helpers.formValidation.formatErrorLine instead.',
    );

    return formatErrorLine(...args);
};

export { formatLine };
