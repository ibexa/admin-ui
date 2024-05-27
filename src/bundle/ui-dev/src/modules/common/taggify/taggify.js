import React, { useState, useEffect, useRef } from 'react';
import PropTypes from 'prop-types';

import Icon from '../icon/icon';
import Tag from '../tag/tag';

const ENTER_CODE = 'Enter';

const Taggify = ({ initialTags, hotKeys, allowDuplicates, onTagsChange, bottomHint }) => {
    const [tags, setTags] = useState([]);
    const [newTagContent, setNewTagContent] = useState('');
    const newTagContentInputRef = useRef(null);
    const addTag = (content) => {
        if ((!allowDuplicates && isDuplicated(content)) || !content) {
            return;
        }

        setTags((prevTags) => {
            const lastTag = prevTags[prevTags.length - 1];
            const nextId = lastTag ? lastTag.id + 1 : 0;

            return [...prevTags, { content, id: nextId }];
        });
        setNewTagContent('');
    };
    const removeTag = (event, id) => {
        event.preventDefault();
        event.stopPropagation();

        const filteredTags = tags.filter((tag) => tag.id !== id);

        setTags(filteredTags);
    };
    const isDuplicated = (content) => {
        const searchedTag = tags.filter((tag) => tag.content === content);

        return !!searchedTag.length;
    };
    const handleInputKeyUp = ({ code }) => {
        if (hotKeys.includes(code)) {
            const parsedContent = code !== ENTER_CODE ? newTagContent.slice(0, -1) : newTagContent;

            addTag(parsedContent);
        }
    };

    useEffect(() => {
        initialTags.map(addTag);
    }, []);

    useEffect(() => {
        const mappedTags = tags.map((tag) => tag.content);

        onTagsChange(mappedTags);
    }, [tags]);
console.log(tags)
    return (
        <div className="c-taggify" onClick={() => newTagContentInputRef.current.focus()}>
            <div className="c-taggify__adding-area">
                <div className="c-taggify__inputs">
                    <input
                        ref={newTagContentInputRef}
                        className="c-taggify__new-tag-input"
                        type="text"
                        value={newTagContent}
                        onChange={({ currentTarget }) => setNewTagContent(currentTarget.value)}
                        onKeyUp={handleInputKeyUp}
                        onBlur={() => addTag(newTagContent)}
                    />
                </div>
                <div className="c-taggify__tags">
                    {tags.map((tag) => (
                        <Tag key={`${tag.id}-key`} content={tag.content} onRemove={(event) => removeTag(event, tag.id)} />
                    ))}
                </div>
            </div>
            {bottomHint && (
                <div className="c-taggify__bottom-hint">
                    <Icon name="system-information" extraClasses="ibexa-icon--tiny-small"></Icon>
                    {bottomHint}
                </div>
            )}
        </div>
    );
};

Taggify.propTypes = {
    initialTags: PropTypes.array,
    hotKeys: PropTypes.array,
    allowDuplicates: PropTypes.bool,
    onTagsChange: PropTypes.func,
    bottomHint: PropTypes.string,
};

Taggify.defaultProps = {
    initialTags: [],
    hotKeys: ['Enter', 'Comma'],
    allowDuplicates: false,
    onTagsChange: () => {},
    bottomHint: '',
};

export default Taggify;

// import React, { useState, useEffect } from 'react';
// import PropTypes from 'prop-types';

// import Tag from '../tag/tag';
// import { createCssClassNames } from '../helpers/css.class.names';
// const ENTER_KEY = 'Enter';

// const Taggify = ({
//     initialTags,
//     initialSourceInputValue,
//     hotKeys,
//     allowDuplicates,
//     sourceInputName,
//     sourceInputClass,
//     newTagInputClass,
//     changeValueCallback
// }) => {
//     const [tags, setTags] = useState([]);
//     const [stringTagsValue, setStringTagsValue] = useEffect(initialSourceInputValue);
//     const [newTagValue, setNewTagValue] = useState('');
//     const sourceInputClassName = createCssClassNames({
//         'c-taggify__input c-taggify__input--source': true,
//         [sourceInputClass]: true,
//     });
//     const newTagInputClassName = createCssClassNames({
//         'c-taggify__input c-taggify__input--new-tag': true,
//         [newTagInputClass]: true,
//     });
//     // const triggerAddTagAction = (event) => {
//     //     const { code } = event;

//     //     if (hotKeys.includes(code)) {
//     //         const tagValueToSet = code === ENTER_KEY ? newTagValue : newTagValue.substring(0, newTagValue.length - 1);

//     //         if (!allowDuplicates && isDuplicated(tagValueToSet)) {
//     //             return;
//     //         }

//     //         if (tagValueToSet) {
//     //             addTag(tagValueToSet);
//     //             setNewTagValue('');
//     //         }
//     //     }
//     // };
//     // const addTag = (label) => {
//     //     setTags((prevTags) => {
//     //         const lastTag = prevTags[prevTags.length - 1];
//     //         const nextId = lastTag ? lastTag.id + 1 : 0;

//     //         return [...prevTags, { label, id: nextId }];
//     //     });
//     // };
//     // const removeTag = (id) => {
//     //     const filteredTags = tags.filter((tag) => tag.id !== id);

//     //     setTags(filteredTags);
//     // };
//     // const isDuplicated = (label) => {
//     //     const searchedTag = tags.filter((tag) => tag.label === label);

//     //     return !!searchedTag.length;
//     // }

//     useEffect(() => {
//         const intitialValue = stringTagsValue ? stringTagsValue.split(',') : initialTags;

//         initialTags.map(addTag);
//     }, []);

//     return (
//         <div className="c-taggify">
//             {/* <div className="c-taggify__tags">
//                 {tags.map((tag) => <Tag key={`${tag.id}-key`} content={tag.label} onRemove={() => removeTag(tag.id)}/>)}
//             </div>
//             <input
//                 className={newTagInputClassName}
//                 type="text"
//                 value={newTagValue}
//                 onChange={({ currentTarget }) => setNewTagValue(currentTarget.value)}
//                 onKeyUp={triggerAddTagAction}
//             /> */}
//             <input
//                 className={sourceInputClassName}
//                 name={sourceInputName}
//                 type="text"
//                 value={tags.map((tag) => tag.label).join(', ')}
//                 onChange={() => {}}
//             />
//         </div>
//     );
// };

// Taggify.propTypes = {
//     initialTags: PropTypes.array,
//     initialSourceInputValue: PropTypes.string,
//     hotKeys: PropTypes.array,
//     allowDuplicates: PropTypes.bool,
//     sourceInputName: PropTypes.string,
//     sourceInputClass: PropTypes.string,
//     changeValueCallback: PropTypes.func,
// };

// Taggify.defaultProps = {
//     initialTags: [],
//     initialSourceInputValue: '',
//     hotKeys: ['Enter', 'Comma'],
//     allowDuplicates: false,
//     sourceInputName: 'ibexa-taggify-source-input',
//     sourceInputClass: '',
//     newTagInputClass: '',
//     changeValueCallback: () => {},
// };

// export default Taggify;
