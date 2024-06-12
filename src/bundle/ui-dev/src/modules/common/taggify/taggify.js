import React, { useState, useEffect, useRef } from 'react';
import PropTypes from 'prop-types';

import Icon from '../icon/icon';
import Tag from '../tag/tag';

const ENTER_CODE = 'Enter';

const Taggify = ({ tagsValue, hotKeys, allowDuplicates, onTagsChange, bottomHint }) => {
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
        tagsValue.map(addTag);
    }, []);

    useEffect(() => {
        const mappedTags = tags.map((tag) => tag.content);

        onTagsChange(mappedTags);
    }, [tags]);

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
    tagsValue: PropTypes.array,
    hotKeys: PropTypes.array,
    allowDuplicates: PropTypes.bool,
    onTagsChange: PropTypes.func,
    bottomHint: PropTypes.string,
};

Taggify.defaultProps = {
    tagsValue: [],
    hotKeys: ['Enter', 'Comma'],
    allowDuplicates: false,
    onTagsChange: () => {},
    bottomHint: '',
};

export default Taggify;
