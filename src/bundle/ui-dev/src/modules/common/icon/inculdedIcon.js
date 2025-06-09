import React from 'react';
import PropTypes from 'prop-types';

import Add from '@ids-assets/img/icons/add.svg';
import AlertError from '@ids-assets/img/icons/alert-error.svg';
import AlertWarning from '@ids-assets/img/icons/alert-warning.svg';
import AppBlog from '@ids-assets/img/icons/app-blog.svg';
import ArrowChevronDown from '@ids-assets/img/icons/arrow-chevron-down.svg';
import ArrowChevronUp from '@ids-assets/img/icons/arrow-chevron-up.svg';
import ArrowExpandLeft from '@ids-assets/img/icons/arrow-expand-left.svg';
import ArrowLeft from '@ids-assets/img/icons/arrow-left.svg';
import ArrowRotate from '@ids-assets/img/icons/arrow-rotate.svg';
import Calendar from '@ids-assets/img/icons/calendar.svg';
import CheckCircle from '@ids-assets/img/icons/check-circle.svg';
import ContentTree from '@ids-assets/img/icons/content-tree.svg';
import Discard from '@ids-assets/img/icons/discard.svg';
import DiscardCircle from '@ids-assets/img/icons/discard-circle.svg';
import Download from '@ids-assets/img/icons/download.svg';
import Drag from '@ids-assets/img/icons/drag.svg';
import Duplicate from '@ids-assets/img/icons/duplicate.svg';
import Edit from '@ids-assets/img/icons/edit.svg';
import File from '@ids-assets/img/icons/file.svg';
import FileText from '@ids-assets/img/icons/file-text.svg';
import Filters from '@ids-assets/img/icons/filters.svg';
import Folder from '@ids-assets/img/icons/folder.svg';
import FormCheck from '@ids-assets/img/icons/form-check.svg';
import FormCheckSquare from '@ids-assets/img/icons/form-check-square.svg';
import FormInput from '@ids-assets/img/icons/form-input.svg';
import Help from '@ids-assets/img/icons/help.svg';
import Image from '@ids-assets/img/icons/image.svg';
import ImageGallery from '@ids-assets/img/icons/image-gallery.svg';
import ImageUpload from '@ids-assets/img/icons/image-upload.svg';
import InfoCircle from '@ids-assets/img/icons/info-circle.svg';
import InfoSquare from '@ids-assets/img/icons/info-square.svg';
import LayoutNavbar from '@ids-assets/img/icons/layout-navbar.svg';
import More from '@ids-assets/img/icons/more.svg';
import NoteBlog from '@ids-assets/img/icons/note-blog.svg';
import PinLocation from '@ids-assets/img/icons/pin-location.svg';
import Product from '@ids-assets/img/icons/product.svg';
import QaFormCheck from '@ids-assets/img/icons/qa-form-check.svg';
import Search from '@ids-assets/img/icons/search.svg';
import Trash from '@ids-assets/img/icons/trash.svg';
import Upload from '@ids-assets/img/icons/upload.svg';
import User from '@ids-assets/img/icons/user.svg';
import UserGroup from '@ids-assets/img/icons/user-group.svg';
import VideoPlay from '@ids-assets/img/icons/video-play.svg';
import ViewGrid from '@ids-assets/img/icons/view-grid.svg';
import ViewList from '@ids-assets/img/icons/view-list.svg';
import Visibility from '@ids-assets/img/icons/visibility.svg';

const bcIconsMap = {
    about: InfoSquare,
    'about-info': Help,
    approved: CheckCircle,
    article: FileText,
    back: ArrowLeft,
    blog_post: NoteBlog,
    blog: AppBlog,
    'caret-down': ArrowChevronDown,
    'caret-up': ArrowChevronUp,
    checkmark: FormCheck,
    'circle-close': DiscardCircle,
    create: Add,
    date: Calendar,
    'expand-left': ArrowExpandLeft,
    fields: FormInput,
    form: FormCheckSquare,
    gallery: ImageGallery,
    landing_page: LayoutNavbar,
    notice: AlertError,
    options: More,
    place: PinLocation,
    'qa-form': QaFormCheck,
    spinner: ArrowRotate,
    'system-information': InfoCircle,
    'upload-image': ImageUpload,
    video: VideoPlay,
    view: Visibility,
    warning: AlertWarning,
};

const iconsMap = {
    ...bcIconsMap,
    add: Add,
    'alert-error': AlertError,
    'alert-warning': AlertWarning,
    'app-blog': AppBlog,
    'arrow-chevron-down': ArrowChevronDown,
    'arrow-chevron-up': ArrowChevronUp,
    'arrow-expand-left': ArrowExpandLeft,
    'arrow-left': ArrowLeft,
    'arrow-rotate': ArrowRotate,
    calendar: Calendar,
    'check-circle': CheckCircle,
    'content-tree': ContentTree,
    discard: Discard,
    'discard-circle': DiscardCircle,
    download: Download,
    drag: Drag,
    duplicate: Duplicate,
    edit: Edit,
    file: File,
    'file-text': FileText,
    filters: Filters,
    folder: Folder,
    'form-check-square': FormCheckSquare,
    'form-check': FormCheck,
    'form-input': FormInput,
    help: Help,
    image: Image,
    'image-gallery': ImageGallery,
    'image-upload': ImageUpload,
    'info-circle': InfoCircle,
    'info-square': InfoSquare,
    'layout-navbar': LayoutNavbar,
    more: More,
    'note-blog': NoteBlog,
    'pin-location': PinLocation,
    product: Product,
    'qa-form-check': QaFormCheck,
    search: Search,
    trash: Trash,
    upload: Upload,
    user_group: UserGroup,
    user: User,
    'video-play': VideoPlay,
    'view-grid': ViewGrid,
    'view-list': ViewList,
    visibility: Visibility,
};

const InculdedIcon = ({ name = 'help', cssClass = '', defaultIconName = 'help' }) => {
    const IconComponent = iconsMap[name] ?? iconsMap[defaultIconName];

    return <IconComponent className={cssClass} />;
};

InculdedIcon.propTypes = {
    cssClass: PropTypes.string,
    name: PropTypes.string,
    defaultIconName: PropTypes.string,
};

export default InculdedIcon;
