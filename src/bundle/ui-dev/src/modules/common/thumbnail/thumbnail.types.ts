import { ThumbnailData } from '../types/common';

export default interface ThumbnailProps extends ThumbnailData {
    iconExtraClasses?: string;
    contentTypeIconPath?: string;
};
