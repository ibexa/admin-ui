import fs from 'node:fs';
import readline from 'node:readline';
import { parse } from '@typescript-eslint/typescript-estree';

const isTranslatorCall = (node) => {
    if (!node || node.type !== 'CallExpression') {
        return false;
    }

    const callee = node.callee;

    return callee?.type === 'MemberExpression'
        && callee.object?.type === 'Identifier'
        && callee.object.name === 'Translator'
        && callee.property?.type === 'Identifier'
        && ['trans', 'transChoice'].includes(callee.property.name);
};

const getStringLiteralValue = (node) => {
    if (!node) {
        return null;
    }

    if (node.type === 'Literal' && typeof node.value === 'string') {
        return node.value;
    }

    return null;
};

const getErrorMessage = (error) => (error instanceof Error ? error.message : String(error));

const formatWarning = (argumentName, node, filePath) => {
    const line = node?.loc?.start?.line ?? 0;
    const column = node?.loc?.start?.column ?? 0;

    return `Could not extract ${argumentName}, expected string literal but got ${node?.type ?? 'nothing'} (in ${filePath} on line ${line} column ${column}).`;
};

const extractFromFile = (filePath) => {
    const source = fs.readFileSync(filePath, 'utf8');
    const ast = parse(source, {
        comment: true,
        jsx: true,
        loc: true,
        range: true,
        filePath,
    });

    const comments = ast.comments ?? [];
    const messages = [];
    const warnings = [];

    const findClosestLeadingComment = (node) => {
        if (!node?.range) {
            return null;
        }

        const [nodeStart] = node.range;
        let candidate = null;

        for (const comment of comments) {
            if (!comment.range || comment.range[1] > nodeStart) {
                continue;
            }

            const textBetween = source.slice(comment.range[1], nodeStart);
            if (!/^\s*$/.test(textBetween)) {
                continue;
            }

            if (candidate === null || comment.range[1] > candidate.range[1]) {
                candidate = comment;
            }
        }

        return candidate;
    };

    const extractDesc = (node) => {
        const comment = findClosestLeadingComment(node);

        if (!comment?.value) {
            return null;
        }

        const match = comment.value.match(/@Desc\((['"])((?:\\[\s\S]|(?!\1)[\s\S])*)\1\)/);

        return match ? match[2].replace(/\\(['"\\])/g, '$1') : null;
    };

    const visit = (node) => {
        if (!node || typeof node !== 'object') {
            return;
        }

        if (isTranslatorCall(node)) {
            const methodName = node.callee.property.name;
            const domainArgIndex = methodName === 'trans' ? 2 : 3;
            const idNode = node.arguments[0];
            const id = getStringLiteralValue(idNode);

            if (id === null) {
                warnings.push(formatWarning('id', idNode, filePath));
            } else {
                const domainNode = node.arguments[domainArgIndex];
                const domain = domainNode ? getStringLiteralValue(domainNode) : null;

                if (domainNode && domain === null) {
                    warnings.push(formatWarning('domain', domainNode, filePath));
                }

                messages.push({
                    id,
                    domain,
                    desc: extractDesc(idNode),
                });
            }
        }

        for (const value of Object.values(node)) {
            if (Array.isArray(value)) {
                value.forEach(visit);
                continue;
            }

            if (value && typeof value === 'object') {
                visit(value);
            }
        }
    };

    visit(ast);

    return { messages, warnings };
};

const [, , mode] = process.argv;

if (mode === '--check-runtime') {
    process.stdout.write('ok');
    process.exit(0);
}

if (mode === '--serve') {
    const rl = readline.createInterface({ input: process.stdin, terminal: false });

    rl.on('line', (filePath) => {
        if (!filePath) {
            process.stdout.write(`${JSON.stringify({ error: 'Empty file path received.' })}\n`);
            return;
        }

        try {
            process.stdout.write(`${JSON.stringify(extractFromFile(filePath))}\n`);
        } catch (error) {
            process.stdout.write(`${JSON.stringify({ error: getErrorMessage(error) })}\n`);
        }
    });

    process.exitCode = 0;
} else {
    const filePath = mode;

    if (!filePath) {
        process.stderr.write('Missing file path argument.\n');
        process.exit(1);
    }

    try {
        process.stdout.write(JSON.stringify(extractFromFile(filePath)));
    } catch (error) {
        process.stderr.write(`${getErrorMessage(error)}\n`);
        process.exit(1);
    }
}
