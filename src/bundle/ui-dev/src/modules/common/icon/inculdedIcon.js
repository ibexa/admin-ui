import React from 'react';
import PropTypes from 'prop-types';

import About from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/about.svg';
import AboutInfo from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/about-info.svg';
import Article from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/article.svg';
import Back from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/back.svg';
import Blog from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/blog.svg';
import BlogPost from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/blog_post.svg';
import CaretDown from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/caret-down.svg';
import CaretUp from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/caret-up.svg';
import Checkmark from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/checkmark.svg';
import ContentTree from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/content-tree.svg';
import Date from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/date.svg';
import Discard from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/discard.svg';
import Drag from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/drag.svg';
import Fields from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/fields.svg';
import File from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/file.svg';
import Folder from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/folder.svg';
import Form from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/form.svg';
import Gallery from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/gallery.svg';
import Image from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/image.svg';
import LandingPage from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/landing_page.svg';
import Place from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/place.svg';
import Product from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/product.svg';
import Search from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/search.svg';
import Spinner from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/spinner.svg';
import Video from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/video.svg';
import View from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/view.svg';
import ViewGrid from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/view-grid.svg';
import ViewList from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/view-list.svg';
import User from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/user.svg';
import UserGroup from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/user_group.svg';
import UploadImage from '@ibexa-admin-ui/src/bundle/Resources/public/img/icons/upload-image.svg';

const iconsMap = {
    about: About,
    'about-info': AboutInfo,
    article: Article,
    back: Back,
    blog: Blog,
    blog_post: BlogPost,
    'caret-down': CaretDown,
    'caret-up': CaretUp,
    checkmark: Checkmark,
    'content-tree': ContentTree,
    date: Date,
    discard: Discard,
    drag: Drag,
    file: File,
    fields: Fields,
    folder: Folder,
    form: Form,
    gallery: Gallery,
    image: Image,
    landing_page: LandingPage,
    place: Place,
    product: Product,
    search: Search,
    spinner: Spinner,
    video: Video,
    view: View,
    'view-grid': ViewGrid,
    'view-list': ViewList,
    'missing-icon': AboutInfo,
    user: User,
    user_group: UserGroup,
    'upload-image': UploadImage,
};

const InculdedIcon = (props) => {
    const { name, cssClass } = props;
    const IconComponent = iconsMap[name] ?? iconsMap['missing-icon'];

    return <IconComponent className={cssClass} />;
};

InculdedIcon.propTypes = {
    cssClass: PropTypes.string,
    name: PropTypes.string,
};

InculdedIcon.defaultProps = {
    cssClass: '',
    name: 'missing-icon',
};

export default InculdedIcon;
