import React, { useState, useEffect, useRef, useImperativeHandle, forwardRef } from 'react';
import PropTypes from 'prop-types';

import Icon from '../icon/icon';
import Tag from '../tag/tag';

const ENTER_CODE = 'Enter';
const COMMA_CODE = 'Comma';

const Taggify = forwardRef(
    ({ hotKeys = [ENTER_CODE, COMMA_CODE], allowDuplicates = false, onTagsChange = () => {}, bottomHint = '' }, ref) => {
        const [tags, setTags] = useState([]);
        const [newTagContent, setNewTagContent] = useState('');
        const newTagContentInputRef = useRef(null);
        const addTags = (inputTags = []) => {
            setTags((prevTags) => {
                const newTags = [...prevTags];
                let nextId = newTags.at(-1)?.id ?? 0;

                inputTags.forEach((inputTagContent) => {
                    const trimmedInputTagContent = inputTagContent.trim();

                    if ((!allowDuplicates && isDuplicated(newTags, trimmedInputTagContent)) || !trimmedInputTagContent) {
                        return;
                    }

                    nextId++;
                    newTags.push({
                        id: nextId,
                        content: trimmedInputTagContent,
                    });
                });

                setNewTagContent('');
                return newTags;
            });
        };
        const removeTag = (event, id) => {
            event.preventDefault();
            event.stopPropagation();

            setTags((prevState) => prevState.filter((tag) => tag.id !== id));
        };
        const isDuplicated = (tagsArr, content) => {
            const searchedTag = tagsArr.filter((tag) => tag.content === content);

            return !!searchedTag.length;
        };
        const handleInputKeyUp = ({ code }) => {
            if (hotKeys.includes(code)) {
                const parsedContent = code !== ENTER_CODE ? newTagContent.slice(0, -1) : newTagContent;

                addTags([parsedContent]);
            }
        };

        useImperativeHandle(
            ref,
            () => ({
                addTags,
            }),
            [addTags],
        );

        useEffect(() => {
            onTagsChange(tags);
        }, [tags]);

        return (
            <div
                className="c-taggify"
                onClick={() => newTagContentInputRef.current.focus()}
                onKeyDown={() => newTagContentInputRef.current.focus()}
            >
                <div className="c-taggify__adding-area">
                    <div className="c-taggify__inputs">
                        <input
                            ref={newTagContentInputRef}
                            className="c-taggify__new-tag-input"
                            type="text"
                            value={newTagContent}
                            onChange={({ currentTarget }) => setNewTagContent(currentTarget.value)}
                            onKeyUp={handleInputKeyUp}
                            onBlur={() => addTags([newTagContent])}
                        />
                    </div>
                    <div className="c-taggify__tags">
                        {tags.map(({ id, content }) => (
                            <Tag key={id} content={content} onRemove={(event) => removeTag(event, id)} />
                        ))}
                    </div>
                </div>
                {bottomHint && (
                    <div className="c-taggify__bottom-hint">
                        <Icon name="system-information" extraClasses="ibexa-icon--tiny-small" />
                        {bottomHint}
                    </div>
                )}
            </div>
        );
    },
);

Taggify.propTypes = {
    hotKeys: PropTypes.array,
    allowDuplicates: PropTypes.bool,
    onTagsChange: PropTypes.func,
    bottomHint: PropTypes.string,
};

Taggify.displayName = 'Taggify';

export default Taggify;
