import { Node } from '@tiptap/core'

function mediaUrl(gif, rendition) {
    const url = gif?.images?.[rendition]?.url

    if (typeof url !== 'string' || url.length === 0) {
        return null
    }

    return url
}

function numberOrNull(value) {
    if (value === null || value === undefined || value === '') {
        return null
    }

    const number = Number(value)

    if (!Number.isFinite(number)) {
        return null
    }

    return Math.trunc(number)
}

window.filamentGiphyPicker = function filamentGiphyPicker(config) {
    return {
        attribution: 'Powered by GIPHY',
        query: '',
        gifs: [],
        offset: 0,
        total: 0,
        loading: false,
        failed: false,
        timer: null,
        pendingReset: false,
        boot() {
            this.load(true)
        },
        onQuery() {
            clearTimeout(this.timer)
            this.timer = setTimeout(() => {
                if (this.loading) {
                    this.pendingReset = true

                    return
                }

                this.load(true)
            }, 300)
        },
        gridRendition(gif) {
            for (const name of [config.gridRendition, 'fixed_width', 'fixed_height']) {
                if (mediaUrl(gif, name) !== null) {
                    return gif.images[name]
                }
            }

            return null
        },
        gridSrc(gif) {
            return this.gridRendition(gif)?.url ?? null
        },
        gridWidth(gif) {
            return numberOrNull(this.gridRendition(gif)?.width)
        },
        gridHeight(gif) {
            return numberOrNull(this.gridRendition(gif)?.height)
        },
        insertedSrc(gif) {
            const url = mediaUrl(gif, config.insertedRendition)

            if (url === null || !url.startsWith('https://')) {
                return null
            }

            return url
        },
        canInsert(gif) {
            return this.gridSrc(gif) !== null && this.insertedSrc(gif) !== null
        },
        hasMore() {
            return this.offset < this.total
        },
        async load(reset) {
            if (this.loading) {
                if (reset) {
                    this.pendingReset = true
                }

                return
            }

            const offset = reset ? 0 : this.offset

            if (reset) {
                this.offset = 0
                this.gifs = []
                this.failed = false
            }

            this.loading = true

            const params = new URLSearchParams({
                api_key: config.apiKey ?? '',
                limit: '20',
                offset: String(offset),
                rating: config.rating,
            })

            if (config.language) {
                params.set('lang', config.language)
            }

            if (config.customerId) {
                params.set('customer_id', String(config.customerId))
            }

            const trimmed = this.query.trim()
            const endpoint =
                trimmed === ''
                    ? 'https://api.giphy.com/v1/gifs/trending'
                    : 'https://api.giphy.com/v1/gifs/search'

            if (trimmed !== '') {
                params.set('q', this.query)
            }

            try {
                const response = await fetch(`${endpoint}?${params.toString()}`)

                if (!response.ok) {
                    throw new Error('GIPHY request failed')
                }

                const body = await response.json()
                const page = Array.isArray(body.data) ? body.data : []

                this.gifs = reset ? page : this.gifs.concat(page)

                const count = body.pagination?.count ?? page.length
                const pageOffset = body.pagination?.offset ?? offset

                this.offset = pageOffset + count
                this.total = body.pagination?.total_count ?? this.gifs.length
                this.failed = false
            } catch (error) {
                this.failed = true
            } finally {
                this.loading = false
            }

            if (this.pendingReset) {
                this.pendingReset = false
                await this.load(true)
            }
        },
        async choose(gif) {
            const src = this.insertedSrc(gif)

            if (src === null || this.gridSrc(gif) === null) {
                return
            }

            const id = gif?.id == null ? '' : String(gif.id)

            if (id === '') {
                return
            }

            const onclick = gif?.analytics?.onclick?.url

            if (typeof onclick === 'string' && onclick.startsWith('https://')) {
                fetch(onclick, { mode: 'no-cors', keepalive: true }).catch(() => {})
            }

            const rendition = gif.images?.[config.insertedRendition] ?? {}
            const index = Math.max((this.$wire.mountedActions?.length ?? 1) - 1, 0)

            await this.$wire.set(`mountedActions.${index}.data`, {
                id,
                src,
                alt: gif.title ? String(gif.title) : 'GIF',
                width: numberOrNull(rendition.width),
                height: numberOrNull(rendition.height),
                username: gif.user?.username ? String(gif.user.username) : '',
                profileUrl: gif.user?.profile_url ? String(gif.user.profile_url) : '',
                sourceUrl: gif.url ? String(gif.url) : '',
            })

            await this.$wire.callMountedAction()
        },
    }
}

export default function giphyExtension() {
    return Node.create({
        name: 'giphy',
        group: 'block',
        atom: true,
        content: '',
        selectable: true,
        draggable: true,

        addAttributes() {
            return {
                id: {
                    default: null,
                    parseHTML: (element) => element.getAttribute('data-giphy-id'),
                },
                src: {
                    default: null,
                    parseHTML: (element) => (element.matches('img') ? element : element.querySelector('img'))?.getAttribute('src') ?? null,
                },
                alt: {
                    default: null,
                    parseHTML: (element) => (element.matches('img') ? element : element.querySelector('img'))?.getAttribute('alt') ?? null,
                },
                width: {
                    default: null,
                    parseHTML: (element) => numberOrNull((element.matches('img') ? element : element.querySelector('img'))?.getAttribute('width')),
                },
                height: {
                    default: null,
                    parseHTML: (element) => numberOrNull((element.matches('img') ? element : element.querySelector('img'))?.getAttribute('height')),
                },
                username: {
                    default: null,
                    parseHTML: (element) => element.getAttribute('data-giphy-username'),
                },
                profileUrl: {
                    default: null,
                    parseHTML: (element) => element.getAttribute('data-giphy-profile-url'),
                },
                sourceUrl: {
                    default: null,
                    parseHTML: (element) => element.getAttribute('data-giphy-source-url'),
                },
            }
        },

        parseHTML() {
            return [
                { tag: 'img[data-giphy-id]', priority: 100 },
                { tag: 'span[data-giphy-id]', priority: 100 },
            ]
        },

        renderHTML({ node }) {
            const username = node.attrs.username || null
            const profileUrl = node.attrs.profileUrl || null
            const sourceUrl = node.attrs.sourceUrl || null
            const src = node.attrs.src || null
            const host = {
                'data-giphy-id': node.attrs.id,
            }

            if (username) {
                host['data-giphy-username'] = username
            }

            if (profileUrl) {
                host['data-giphy-profile-url'] = profileUrl
            }

            if (sourceUrl) {
                host['data-giphy-source-url'] = sourceUrl
            }

            const children = []

            if (typeof src === 'string' && src.startsWith('https://')) {
                const image = {
                    ...host,
                    src,
                    alt: node.attrs.alt || 'GIF',
                }

                if (node.attrs.width) {
                    image.width = node.attrs.width
                }

                if (node.attrs.height) {
                    image.height = node.attrs.height
                }

                children.push(['img', image])
            } else {
                children.push(['span', host])
            }

            return ['figure', { class: 'fi-giphy-figure' }, ...children]
        },
    })
}
