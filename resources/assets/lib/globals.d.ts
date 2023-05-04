// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare module 'mods.json' {
  export interface ModJson {
    icon?: {
      family: string;
      icon: string;
      weight: string | null;
    };
    name: string;
    type: string;
  }

  const mods: Partial<Record<string, ModJson>>;

  export default mods;
}

// Scoping to prevent global type import pollution.
// There interfaces are only used in this file.
declare module 'legacy-modules' {
  type BeatmapsetDiscussionJson = import('interfaces/beatmapset-discussion-json').default;

  interface BeatmapDiscussionHelperClass {
    url(options: any, useCurrent?: boolean): string;
    urlParse(urlString: string, discussions?: BeatmapsetDiscussionJson[] | null, options?: any): {
      beatmapId?: number;
      beatmapsetId?: number;
      discussionId?: number;
      filter: string;
      mode: string;
      postId?: number;
      user?: number;
    };
  }

  interface TooltipDefault {
    remove: (el: HTMLElement) => void;
  }
}

// #region Extensions to global object types
interface JQueryStatic {
  publish: (eventName: string, data?: any) => void;
  subscribe: (eventName: string, handler: (...params: any[]) => void) => void;
  unsubscribe: (eventName: string, handler?: unknown) => void;
}

interface Window {
  newBody?: HTMLElement;
  newUrl?: URL | Location | null;
}
// #endregion

// #region interfaces for using process.env
interface Process {
  env: ProcessEnv;
}

interface ProcessEnv {
  [key: string]: string | undefined;
}

declare const process: Process;
// #endregion

// TODO: Turbolinks 5.3 is Typescript, so this should be updated then...or it could be never released.
declare const Turbolinks: import('turbolinks').default;

// our helpers
declare const tooltipDefault: import('legacy-modules').TooltipDefault;

// external (to typescript) classes
declare const BeatmapDiscussionHelper: import('legacy-modules').BeatmapDiscussionHelperClass;
declare const fallbackLocale: string;
declare const currentLocale: string;
