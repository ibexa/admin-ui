import fs from 'fs';
import path from 'path';

export const EMPTY_RICHTEXT = {
  xml: '<?xml version="1.0" encoding="UTF-8"?><section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0"><para/></section>',
};

/**
 * Lightweight REST API client for Ibexa DXP.
 * Port of IbexaApiClient.php — used to set up test data before browser tests.
 */
export class IbexaApiClient {
  private readonly baseUrl: string;
  private sessionCookie: string = '';
  private csrfToken: string = '';
  private contentTypeIdCache: Record<string, number> = {};
  private locationIdCache: Record<string, number> = {};
  private contentIdByLocationCache: Record<string, number> = {};

  constructor(
    baseUrl: string,
    private readonly username: string = 'admin',
    private readonly password: string = 'publish',
  ) {
    this.baseUrl = baseUrl.replace(/\/$/, '');
  }

  async init(): Promise<void> {
    let lastError: Error | undefined;
    for (let attempt = 0; attempt < 5; attempt++) {
      try {
        await this.initSession(this.username, this.password);
        return;
      } catch (e) {
        lastError = e as Error;
        if (attempt < 4) await new Promise(r => setTimeout(r, 3000 * (attempt + 1)));
      }
    }
    throw lastError;
  }

  private async initSession(username: string, password: string): Promise<void> {
    const url = `${this.baseUrl}/api/ibexa/v2/user/sessions`;
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/vnd.ibexa.api.SessionInput+json',
        'Accept': 'application/vnd.ibexa.api.Session+json',
        'Accept-Language': 'en-GB',
        'X-Siteaccess': 'admin',
        'Connection': 'close',
      },
      body: JSON.stringify({
        SessionInput: { login: username, password },
      }),
    });

    if (!response.ok) {
      const body = await response.text();
      throw new Error(`Failed to create REST API session (HTTP ${response.status}): ${body}`);
    }

    // Collect Set-Cookie headers
    const setCookieHeaders = response.headers.getSetCookie?.() ?? [];
    if (setCookieHeaders.length > 0) {
      this.sessionCookie = setCookieHeaders
        .map(h => h.split(';')[0])
        .join('; ');
    } else {
      // Fallback for environments where getSetCookie() is not available
      const rawCookie = response.headers.get('set-cookie') ?? '';
      this.sessionCookie = rawCookie.split(',').map(c => c.split(';')[0].trim()).join('; ');
    }

    const data = await response.json() as { Session: { csrfToken: string } };
    this.csrfToken = data.Session?.csrfToken ?? '';

    if (!this.csrfToken) {
      throw new Error(`REST API session created but no CSRF token returned`);
    }
  }

  /**
   * Creates a content type with given field definitions.
   * Returns the content type ID (creates if not exists, looks up if already exists).
   */
  async createContentType(
    name: string,
    identifier: string,
    fields: Array<{
      identifier: string;
      fieldType: string;
      name: string;
      isTranslatable?: boolean;
      isSearchable?: boolean;
      settings?: Record<string, unknown>;
    }>,
    language: string = 'eng-GB',
  ): Promise<number> {
    // Check if already exists
    try {
      return await this.getContentTypeId(identifier);
    } catch {
      // doesn't exist yet, create it
    }

    let position = 1;
    const fieldDefinitions = fields.map(field => {
      const definition: Record<string, unknown> = {
        identifier: field.identifier,
        fieldType: field.fieldType,
        fieldGroup: 'content',
        position: position++,
        isTranslatable: field.isTranslatable ?? true,
        isRequired: false,
        isSearchable: field.isSearchable ?? false,
        names: {
          value: [
            { _languageCode: language, '#text': field.name },
          ],
        },
      };
      if (field.settings) {
        definition.fieldSettings = field.settings;
      }
      return definition;
    });

    const body = {
      ContentTypeCreate: {
        identifier,
        mainLanguageCode: language,
        nameSchema: '<name>',
        urlAliasSchema: '',
        names: {
          value: [
            { _languageCode: language, '#text': name },
          ],
        },
        FieldDefinitions: {
          FieldDefinition: fieldDefinitions,
        },
      },
    };

    // Content type group 1 = "Content"
    const response = await this.request(
      'POST',
      '/api/ibexa/v2/content/typegroups/1/types?publish=true',
      body,
      'application/vnd.ibexa.api.ContentTypeCreate+json',
      'application/vnd.ibexa.api.ContentType+json',
    ) as { ContentType: Record<string, unknown> };

    const ct = response.ContentType;
    let typeId = (ct.id ?? ct._id) as number | undefined;
    if (!typeId && ct._href) {
      const href = ct._href as string;
      typeId = parseInt(href.substring(href.lastIndexOf('/') + 1), 10);
    }
    if (!typeId) throw new Error(`Could not extract content type ID for '${identifier}'`);

    this.contentTypeIdCache[identifier] = typeId;
    return typeId;
  }

  /**
   * Creates a folder content item and returns its content ID.
   */
  async createFolder(name: string, parent: string | number, language: string = 'eng-GB'): Promise<number> {
    return this.createContentItem('folder', parent, language, {
      name,
      short_name: name,
    });
  }

  /**
   * Returns the main location ID for a content item.
   */
  async getMainLocationId(contentId: number): Promise<number> {
    const response = await this.request(
      'GET',
      `/api/ibexa/v2/content/objects/${contentId}/locations`,
      null,
      null,
      'application/vnd.ibexa.api.LocationList+json',
    ) as { LocationList: { Location: unknown } };

    let locations = response.LocationList?.Location as Record<string, unknown>[];
    if (!Array.isArray(locations)) {
      locations = [locations as Record<string, unknown>];
    }

    const location = locations[0];
    if (!location) throw new Error(`No locations found for content ID ${contentId}`);

    let locationId = (location.id ?? location._id) as number | undefined;
    if (!locationId && location._href) {
      const href = location._href as string;
      locationId = parseInt(href.substring(href.lastIndexOf('/') + 1), 10);
    }
    if (!locationId) throw new Error(`Could not extract location ID for content ${contentId}`);

    return locationId;
  }

  /**
   * Creates a content item and returns its content ID.
   */
  async createUser(
    login: string,
    email: string,
    password: string,
    firstName: string,
    lastName: string,
    userGroupRemoteId: string,
    language: string = 'eng-GB',
  ): Promise<number> {
    try {
      return await this.getUserIdByLogin(login);
    } catch {
      // user doesn't exist yet, create it
    }

    const body = {
      UserCreate: {
        mainLanguageCode: language,
        login,
        email,
        password,
        enabled: true,
        UserGroups: {
          UserGroup: [
            { _href: `/api/ibexa/v2/user/groups/remote:${userGroupRemoteId}` },
          ],
        },
        fields: {
          field: [
            { fieldDefinitionIdentifier: 'first_name', languageCode: language, fieldValue: firstName },
            { fieldDefinitionIdentifier: 'last_name', languageCode: language, fieldValue: lastName },
          ],
        },
      },
    };

    const response = await this.request(
      'POST',
      `/api/ibexa/v2/user/users`,
      body,
      'application/vnd.ibexa.api.UserCreate+json',
      'application/vnd.ibexa.api.User+json',
    ) as { User: Record<string, unknown> };

    const user = response.User;
    let userId = (user.id ?? user._id) as number | undefined;
    if (!userId && user._href) {
      const href = user._href as string;
      userId = parseInt(href.substring(href.lastIndexOf('/') + 1), 10);
    }
    if (!userId) throw new Error(`Could not extract user ID for '${login}'`);

    return userId;
  }

  private async getUserIdByLogin(login: string): Promise<number> {
    const response = await this.request(
      'GET',
      `/api/ibexa/v2/user/users?login=${encodeURIComponent(login)}`,
      null,
      null,
      'application/vnd.ibexa.api.UserList+json',
    ) as { UserList: { User: unknown } };

    const users = response.UserList?.User;
    if (!users || (Array.isArray(users) && users.length === 0)) {
      throw new Error(`User '${login}' not found`);
    }

    const user = (Array.isArray(users) ? users[0] : users) as Record<string, unknown>;
    let userId = (user.id ?? user._id) as number | undefined;
    if (!userId && user._href) {
      const href = user._href as string;
      userId = parseInt(href.substring(href.lastIndexOf('/') + 1), 10);
    }
    if (!userId) throw new Error(`Could not extract user ID for '${login}'`);

    return userId;
  }

  async createContentItem(
    contentTypeIdentifier: string,
    parent: string | number,
    language: string,
    fields: Record<string, unknown>,
    creatorId?: number,
  ): Promise<number> {
    const contentTypeId = await this.getContentTypeId(contentTypeIdentifier);
    const parentLocationId = typeof parent === 'number'
      ? parent
      : await this.getLocationIdByPath(parent);

    const fieldList = await Promise.all(
      Object.entries(fields).map(async ([identifier, value]) => ({
        fieldDefinitionIdentifier: identifier,
        languageCode: language,
        fieldValue: await this.encodeFieldValue(value),
      })),
    );

    const contentCreate: Record<string, unknown> = {
      ContentType: {
        _href: `/api/ibexa/v2/content/types/${contentTypeId}`,
      },
      mainLanguageCode: language,
      LocationCreate: {
        ParentLocation: {
          _href: `/api/ibexa/v2/content/locations/${parentLocationId}`,
        },
        priority: 0,
        hidden: false,
        sortField: 'PATH',
        sortOrder: 'ASC',
      },
      fields: {
        field: fieldList,
      },
    };

    if (creatorId !== undefined) {
      contentCreate.User = { _href: `/api/ibexa/v2/user/users/${creatorId}` };
    }

    const body = { ContentCreate: contentCreate };

    const response = await this.request(
      'POST',
      '/api/ibexa/v2/content/objects',
      body,
      'application/vnd.ibexa.api.ContentCreate+json',
      'application/vnd.ibexa.api.Content+json',
    ) as { Content: Record<string, unknown> };

    const content = response.Content;
    let contentId = (content.id ?? content._id) as number | undefined;
    if (!contentId && content._href) {
      const href = content._href as string;
      contentId = parseInt(href.substring(href.lastIndexOf('/') + 1), 10);
    }
    if (!contentId) throw new Error('Could not extract content ID from response');

    const currentVersionHref = (content.CurrentVersion as Record<string, string>)._href;
    let versionNo: number;
    const versionMatch = currentVersionHref.match(/\/versions\/(\d+)$/);
    if (versionMatch) {
      versionNo = parseInt(versionMatch[1], 10);
    } else {
      const cvResponse = await this.request('GET', currentVersionHref, null, null, 'application/vnd.ibexa.api.Version+json') as {
        Version: { VersionInfo: { versionNo: number } }
      };
      versionNo = cvResponse.Version.VersionInfo.versionNo ?? 1;
    }

    await this.publishVersion(contentId, versionNo);

    const locationId = await this.getMainLocationId(contentId);
    this.locationIdCache[`content:${contentId}`] = locationId;

    return contentId;
  }

  /**
   * Updates content by creating a new draft and publishing it. Returns new version number.
   */
  async updateContent(contentId: number, language: string, fields: Record<string, unknown>, _creatorId?: number): Promise<number> {
    const draftVersionNo = await this.createVersionDraft(contentId);
    await this.updateDraft(contentId, draftVersionNo, language, fields);
    await this.publishVersion(contentId, draftVersionNo);
    return draftVersionNo;
  }

  /**
   * Creates a draft (unpublished) of content. Returns draft version number.
   */
  async createDraft(contentId: number, language: string, fields: Record<string, unknown>): Promise<number> {
    const draftVersionNo = await this.createVersionDraft(contentId);
    await this.updateDraft(contentId, draftVersionNo, language, fields);
    return draftVersionNo;
  }

  /**
   * Resolves content ID by location path (e.g. "Media/Images").
   */
  async getContentIdByPath(locationPath: string): Promise<number> {
    const locationId = await this.getLocationIdByPath(locationPath);

    const cacheKey = `loc:${locationId}`;
    if (this.contentIdByLocationCache[cacheKey] !== undefined) {
      return this.contentIdByLocationCache[cacheKey];
    }

    const response = await this.request(
      'POST',
      '/api/ibexa/v2/views',
      {
        ViewInput: {
          identifier: `find-by-location-${locationId}-${Date.now()}`,
          public: false,
          Query: {
            Filter: { LocationIdCriterion: locationId },
            limit: 1,
            offset: 0,
          },
        },
      },
      'application/vnd.ibexa.api.ViewInput+json',
      'application/vnd.ibexa.api.View+json',
    ) as { View: { Result: { searchHits: { searchHit: unknown } } } };

    let hits = response.View?.Result?.searchHits?.searchHit as Record<string, unknown>[];
    if (!Array.isArray(hits)) hits = [hits as Record<string, unknown>];

    const hit = hits[0];
    if (!hit) throw new Error(`No content found for location ID ${locationId}`);

    const contentHref = ((hit.value as Record<string, unknown>)?.Content as Record<string, unknown>)?._href as string;
    if (!contentHref) throw new Error(`Could not extract content href for location ${locationId}`);

    const contentId = parseInt(contentHref.substring(contentHref.lastIndexOf('/') + 1), 10);
    this.contentIdByLocationCache[cacheKey] = contentId;
    return contentId;
  }

  private async getContentTypeId(identifier: string): Promise<number> {
    if (this.contentTypeIdCache[identifier] !== undefined) {
      return this.contentTypeIdCache[identifier];
    }

    const response = await this.request(
      'GET',
      `/api/ibexa/v2/content/types?identifier=${identifier}`,
      null,
      null,
      'application/vnd.ibexa.api.ContentTypeList+json',
    ) as { ContentTypeList: { ContentType: unknown } };

    const types = response.ContentTypeList?.ContentType;
    if (!types || (Array.isArray(types) && types.length === 0)) {
      throw new Error(`Content type '${identifier}' not found`);
    }

    const type = (Array.isArray(types) ? types[0] : types) as Record<string, unknown>;
    if (!type) throw new Error(`Content type '${identifier}' found but structure unexpected`);

    let id = (type.id ?? type._id) as number | undefined;
    if (!id && type._href) {
      const href = type._href as string;
      id = parseInt(href.substring(href.lastIndexOf('/') + 1), 10);
    }
    if (!id) throw new Error(`Could not extract content type ID for '${identifier}'`);

    this.contentTypeIdCache[identifier] = id;
    return id;
  }

  private async getLocationIdByPath(locationPath: string): Promise<number> {
    if (this.locationIdCache[locationPath] !== undefined) {
      return this.locationIdCache[locationPath];
    }

    const segments = locationPath.replace(/^\/|\/$/g, '').split('/');
    let currentLocationId: number;

    if (segments[0] === 'root' || segments[0] === '') {
      segments.shift();
      currentLocationId = 2; // Ibexa DXP content node
    } else {
      currentLocationId = 1; // hidden tree root
    }

    for (const segment of segments) {
      const cacheKey = `${currentLocationId}/${segment}`;
      if (this.locationIdCache[cacheKey] !== undefined) {
        currentLocationId = this.locationIdCache[cacheKey];
        continue;
      }
      currentLocationId = await this.findChildLocationByName(currentLocationId, segment);
      this.locationIdCache[cacheKey] = currentLocationId;
    }

    this.locationIdCache[locationPath] = currentLocationId;
    return currentLocationId;
  }

  private async findChildLocationByName(parentLocationId: number, name: string): Promise<number> {
    let offset = 0;
    const limit = 100;

    while (true) {
      const response = await this.request(
        'POST',
        '/api/ibexa/v2/views',
        {
          ViewInput: {
            identifier: `find-child-${parentLocationId}-${offset}-${Date.now()}`,
            public: false,
            Query: {
              Filter: { ParentLocationIdCriterion: parentLocationId },
              limit,
              offset,
            },
          },
        },
        'application/vnd.ibexa.api.ViewInput+json',
        'application/vnd.ibexa.api.View+json',
      ) as { View: { Result: { count: number; searchHits: { searchHit: unknown } } } };

      const result = response.View?.Result;
      const total = result?.count ?? 0;
      let hits = result?.searchHits?.searchHit as Record<string, unknown>[];
      if (!Array.isArray(hits)) hits = hits ? [hits as Record<string, unknown>] : [];

      for (const hit of hits) {
        const content = (hit.value as Record<string, unknown>)?.Content as Record<string, unknown>;
        const contentName = (content?.Name ?? content?.TranslatedName ?? '') as string;

        if (contentName !== name) continue;

        const mainLocationHref = (content?.MainLocation as Record<string, string>)?._href;
        if (!mainLocationHref) continue;

        const locationId = parseInt(mainLocationHref.substring(mainLocationHref.lastIndexOf('/') + 1), 10);

        const contentHref = content?._href as string | undefined;
        if (contentHref) {
          const contentId = parseInt(contentHref.substring(contentHref.lastIndexOf('/') + 1), 10);
          this.contentIdByLocationCache[`loc:${locationId}`] = contentId;
        }

        return locationId;
      }

      offset += limit;
      if (offset >= total) break;
    }

    throw new Error(`Location '${name}' not found under location ${parentLocationId}`);
  }

  /**
   * Encodes binary field values (image/binaryfile/media) to base64 for the REST API.
   */
  private async encodeFieldValue(value: unknown): Promise<unknown> {
    if (typeof value !== 'object' || value === null || !('path' in value)) {
      return value;
    }

    const fieldValue = value as { path: string };
    let filePath = fieldValue.path;

    // Resolve relative paths against the project root.
    // __dirname = tests/playwright_ts/lib/ → go up 6 levels to project root:
    // lib/ → playwright_ts/ → tests/ → version-comparison/ → ibexa/ → vendor/ → project root
    if (!path.isAbsolute(filePath)) {
      filePath = path.join(__dirname, '../../../../../../', filePath);
    }

    if (!fs.existsSync(filePath)) {
      return value;
    }

    const data = fs.readFileSync(filePath);
    return {
      fileName: path.basename(filePath),
      fileSize: data.length,
      data: data.toString('base64'),
    };
  }

  private async createVersionDraft(contentId: number): Promise<number> {
    const response = await this.request(
      'COPY',
      `/api/ibexa/v2/content/objects/${contentId}/currentversion`,
      null,
      null,
      'application/vnd.ibexa.api.Version+json',
    ) as { Version: { VersionInfo?: { _href?: string }; _href?: string } };

    const versionHref = response.Version.VersionInfo?._href ?? response.Version._href ?? '';
    return parseInt(versionHref.substring(versionHref.lastIndexOf('/') + 1), 10);
  }

  private async updateDraft(contentId: number, versionNo: number, language: string, fields: Record<string, unknown>): Promise<void> {
    const fieldList = await Promise.all(
      Object.entries(fields).map(async ([identifier, value]) => ({
        fieldDefinitionIdentifier: identifier,
        languageCode: language,
        fieldValue: await this.encodeFieldValue(value),
      })),
    );

    const body = {
      VersionUpdate: {
        fields: {
          field: fieldList,
        },
      },
    };

    await this.request(
      'PATCH',
      `/api/ibexa/v2/content/objects/${contentId}/versions/${versionNo}`,
      body,
      'application/vnd.ibexa.api.VersionUpdate+json',
      'application/vnd.ibexa.api.Version+json',
    );
  }

  private async publishVersion(contentId: number, versionNo: number): Promise<void> {
    await this.request(
      'PUBLISH',
      `/api/ibexa/v2/content/objects/${contentId}/versions/${versionNo}`,
      null,
      null,
      null,
    );
  }

  async hideLocation(locationId: number): Promise<void> {
    const result = await this.request(
      'PATCH',
      `/api/ibexa/v2/content/locations/${locationId}`,
      { LocationUpdate: { hidden: true } },
      'application/vnd.ibexa.api.LocationUpdate+json',
      'application/vnd.ibexa.api.Location+json',
    ) as { Location?: { hidden?: boolean; id?: number } };
  }

  private async request(
    method: string,
    path: string,
    body: unknown,
    contentType: string | null,
    accept: string | null,
  ): Promise<unknown> {
    const url = `${this.baseUrl}${path}`;
    const headers: Record<string, string> = {
      'Cookie': this.sessionCookie,
      'X-CSRF-Token': this.csrfToken,
      'X-Siteaccess': 'admin',
      'Connection': 'close',
    };

    if (contentType) headers['Content-Type'] = contentType;
    if (accept) headers['Accept'] = accept;
    headers['Accept-Language'] = 'en-GB';

    let lastError: Error | undefined;
    for (let attempt = 0; attempt < 5; attempt++) {
      try {
        const response = await fetch(url, {
          method,
          headers,
          body: body !== null ? JSON.stringify(body) : undefined,
          redirect: 'follow',
        });

        if (response.status >= 400) {
          const responseBody = await response.text();
          throw new Error(`API request ${method} ${url} failed with status ${response.status}: ${responseBody}`);
        }

        const text = await response.text();
        if (!text) return {};

        return JSON.parse(text);
      } catch (e) {
        lastError = e as Error;
        if (attempt < 4) await new Promise(r => setTimeout(r, 3000 * (attempt + 1)));
      }
    }
    throw lastError;
  }

  async deleteLanguageByCode(code: string): Promise<void> {
    await this.request('DELETE', `/api/ibexa/v2/languages/${code}`, null, null, null).catch(() => {});
  }

  async deleteSectionByName(name: string): Promise<void> {
    const sections = await this.request('GET', '/api/ibexa/v2/content/sections', null, null, 'application/vnd.ibexa.api.SectionList+json') as { SectionList?: { Section?: unknown } };
    let list = sections?.SectionList?.Section ?? [];
    if (!Array.isArray(list)) list = [list];
    for (const s of list as Array<{ identifier?: string; _href?: string; name?: string }>) {
      if (s.identifier === name || s.name === name) {
        const href = s._href ?? '';
        if (href) await this.request('DELETE', href, null, null, null).catch(() => {});
      }
    }
  }

  async deleteContentTypeGroupByName(name: string): Promise<void> {
    const groups = await this.request('GET', '/api/ibexa/v2/content/typegroups', null, null, 'application/vnd.ibexa.api.ContentTypeGroupList+json') as { ContentTypeGroupList?: { ContentTypeGroup?: unknown } };
    let list = groups?.ContentTypeGroupList?.ContentTypeGroup ?? [];
    if (!Array.isArray(list)) list = [list];
    for (const g of list as Array<{ identifier?: string; _href?: string }>) {
      if (g.identifier === name) {
        const href = g._href ?? '';
        if (href) await this.request('DELETE', href, null, null, null).catch(() => {});
      }
    }
  }

  async deleteObjectStateGroupByName(name: string): Promise<void> {
    const groups = await this.request('GET', '/api/ibexa/v2/content/objectstategroups', null, null, 'application/vnd.ibexa.api.ObjectStateGroupList+json') as { ObjectStateGroupList?: { ObjectStateGroup?: unknown } };
    let list = groups?.ObjectStateGroupList?.ObjectStateGroup ?? [];
    if (!Array.isArray(list)) list = [list];
    for (const g of list as Array<{ defaultLanguageCode?: string; names?: { value?: Array<{ _languageCode?: string; '#text'?: string }> }; _href?: string }>) {
      const nameVal = g.names?.value?.find((v) => v._languageCode === 'eng-GB')?.['#text'] ?? '';
      if (nameVal === name) {
        const href = g._href ?? '';
        if (href) await this.request('DELETE', href, null, null, null).catch(() => {});
      }
    }
  }

  async deleteContent(contentId: number): Promise<void> {
    await this.request('DELETE', `/api/ibexa/v2/content/objects/${contentId}`, null, null, null).catch(() => {});
  }

  async deleteRoleByName(name: string): Promise<void> {
    const roles = await this.request('GET', '/api/ibexa/v2/user/roles', null, null, 'application/vnd.ibexa.api.RoleList+json') as { RoleList?: { Role?: unknown } };
    let list = roles?.RoleList?.Role ?? [];
    if (!Array.isArray(list)) list = [list];
    for (const r of list as Array<{ identifier?: string; _href?: string }>) {
      if (r.identifier === name) {
        const href = r._href ?? '';
        if (href) await this.request('DELETE', href, null, null, null).catch(() => {});
      }
    }
  }
}
