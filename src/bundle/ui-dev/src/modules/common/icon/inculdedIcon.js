import React from 'react';
import PropTypes from 'prop-types';

import About from '@ids-assets/img/icons/info-square.svg';
import AboutInfo from '@ids-assets/img/icons/help.svg';
import Approved from '@ids-assets/img/icons/check-circle.svg';
import Article from '@ids-assets/img/icons/file-text.svg';
import Back from '@ids-assets/img/icons/arrow-left.svg';
import Blog from '@ids-assets/img/icons/app-blog.svg';
import BlogPost from '@ids-assets/img/icons/note-blog.svg';
import CaretDown from '@ids-assets/img/icons/arrow-chevron-down.svg';
import CaretUp from '@ids-assets/img/icons/arrow-chevron-up.svg';
import CircleClose from '@ids-assets/img/icons/discard-circle.svg';
import Create from '@ids-assets/img/icons/add.svg';
import Checkmark from '@ids-assets/img/icons/form-check.svg';
import ContentTree from '@ids-assets/img/icons/content-tree.svg';
import Date from '@ids-assets/img/icons/calendar.svg';
import Discard from '@ids-assets/img/icons/discard.svg';
import Drag from '@ids-assets/img/icons/drag.svg';
import Download from '@ids-assets/img/icons/download.svg';
// import Duplicate from '@ids-assets/img/icons/duplicate.svg'; // TODO: missing icon
import Edit from '@ids-assets/img/icons/edit.svg';
import ExpandLeft from '@ids-assets/img/icons/arrow-expand-left.svg';
import Fields from '@ids-assets/img/icons/form-input.svg';
import File from '@ids-assets/img/icons/file.svg';
import Filters from '@ids-assets/img/icons/filters.svg';
import Folder from '@ids-assets/img/icons/folder.svg';
import Form from '@ids-assets/img/icons/form-check-square.svg';
import Gallery from '@ids-assets/img/icons/image-gallery.svg';
import Image from '@ids-assets/img/icons/image.svg';
import LandingPage from '@ids-assets/img/icons/layout-navbar.svg';
import Notice from '@ids-assets/img/icons/alert-error.svg';
import Options from '@ids-assets/img/icons/more.svg';
import Place from '@ids-assets/img/icons/pin-location.svg';
import Product from '@ids-assets/img/icons/product.svg';
import QaForm from '@ids-assets/img/icons/qa-form-check.svg';
import Search from '@ids-assets/img/icons/search.svg';
import Spinner from '@ids-assets/img/icons/arrow-rotate.svg';
import SystemInformation from '@ids-assets/img/icons/info-circle.svg';
import Trash from '@ids-assets/img/icons/trash.svg';
import Video from '@ids-assets/img/icons/video-play.svg';
import View from '@ids-assets/img/icons/visibility.svg';
import ViewGrid from '@ids-assets/img/icons/view-grid.svg';
import ViewList from '@ids-assets/img/icons/view-list.svg';
import User from '@ids-assets/img/icons/user.svg';
import UserGroup from '@ids-assets/img/icons/user-group.svg';
import Upload from '@ids-assets/img/icons/upload.svg';
import UploadImage from '@ids-assets/img/icons/image-upload.svg';
import Warning from '@ids-assets/img/icons/alert-warning.svg';

const iconsMap = {
    about: About,
    'about-info': AboutInfo,
    approved: Approved,
    article: Article,
    back: Back,
    blog: Blog,
    blog_post: BlogPost,
    'caret-down': CaretDown,
    'caret-up': CaretUp,
    'circle-close': CircleClose,
    create: Create,
    checkmark: Checkmark,
    'content-tree': ContentTree,
    date: Date,
    discard: Discard,
    drag: Drag,
    download: Download,
    duplicate: null, // Duplicate,
    'expand-left': ExpandLeft,
    edit: Edit,
    file: File,
    filters: Filters,
    fields: Fields,
    folder: Folder,
    form: Form,
    gallery: Gallery,
    image: Image,
    landing_page: LandingPage,
    notice: Notice,
    options: Options,
    place: Place,
    product: Product,
    'qa-form': QaForm,
    search: Search,
    spinner: Spinner,
    'system-information': SystemInformation,
    trash: Trash,
    video: Video,
    view: View,
    'view-grid': ViewGrid,
    'view-list': ViewList,
    user: User,
    user_group: UserGroup,
    upload: Upload,
    'upload-image': UploadImage,
    warning: Warning,
};

const InculdedIcon = ({ name = 'about-info', cssClass = '', defaultIconName = 'about-info' }) => {
    const IconComponent = iconsMap[name] ?? iconsMap[defaultIconName];

    return <IconComponent className={cssClass} />;
};

InculdedIcon.propTypes = {
    cssClass: PropTypes.string,
    name: PropTypes.string,
    defaultIconName: PropTypes.string,
};

export default InculdedIcon;
