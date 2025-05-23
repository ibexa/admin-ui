import React from 'react';
import PropTypes from 'prop-types';

import About from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/about.svg';
import AboutInfo from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/about-info.svg';
import Approved from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/approved.svg';
import Article from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/article.svg';
import Back from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/back.svg';
import Blog from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/blog.svg';
import BlogPost from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/blog_post.svg';
import CaretDown from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/caret-down.svg';
import CaretUp from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/caret-up.svg';
import CircleClose from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/circle-close.svg';
import Create from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/create.svg';
import Checkmark from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/checkmark.svg';
import ContentTree from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/content-tree.svg';
import Date from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/date.svg';
import Discard from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/discard.svg';
import Drag from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/drag.svg';
import Download from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/download.svg';
import Duplicate from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/duplicate.svg';
import Edit from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/edit.svg';
import ExpandLeft from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/expand-left.svg';
import Fields from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/fields.svg';
import File from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/file.svg';
import Filters from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/filters.svg';
import Folder from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/folder.svg';
import Form from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/form.svg';
import Gallery from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/gallery.svg';
import Image from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/image.svg';
import LandingPage from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/landing_page.svg';
import Notice from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/notice.svg';
import Options from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/options.svg';
import Place from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/place.svg';
import Product from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/product.svg';
import QaForm from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/qa-form.svg';
import Search from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/search.svg';
import Spinner from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/spinner.svg';
import SystemInformation from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/system-information.svg';
import Trash from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/trash.svg';
import Video from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/video.svg';
import View from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/view.svg';
import ViewGrid from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/view-grid.svg';
import ViewList from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/view-list.svg';
import User from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/user.svg';
import UserGroup from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/user_group.svg';
import Upload from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/upload.svg';
import UploadImage from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/upload-image.svg';
import Warning from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/warning.svg';

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
    duplicate: Duplicate,
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
