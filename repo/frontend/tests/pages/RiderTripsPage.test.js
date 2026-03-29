import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'

const { getMock, postMock, pushMock } = vi.hoisted(() => ({
  getMock: vi.fn(),
  postMock: vi.fn(),
  pushMock: vi.fn(),
}))

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: pushMock,
  }),
}))

vi.mock('@/stores/authStore', () => ({
  useAuthStore: () => ({
    user: { id: 5, username: 'rider01', role: 'rider' },
    logout: vi.fn(),
  }),
}))

vi.mock('@/services/api', () => ({
  default: {
    get: getMock,
    post: postMock,
  },
}))

import RiderTripsPage from '@/pages/rider/RiderTripsPage.vue'

describe('RiderTripsPage', () => {
  const allOrders = [
    { id: 11, status: 'matching', origin_address: 'A', destination_address: 'B' },
    { id: 12, status: 'exception', origin_address: 'C', destination_address: 'D' },
    { id: 13, status: 'completed', origin_address: 'E', destination_address: 'F' },
  ]

  beforeEach(() => {
    vi.clearAllMocks()

    getMock.mockImplementation((_url, config = {}) => {
      const status = config.params?.status
      const data = status ? allOrders.filter((order) => order.status === status) : allOrders

      return Promise.resolve({ data: { data } })
    })
  })

  it('shows exception status tab and filters rider trips by exception', async () => {
    const wrapper = mount(RiderTripsPage, {
      global: {
        stubs: {
          AppShell: { template: '<div><slot /></div>' },
          Input: { template: '<input />' },
          Button: { template: '<button><slot /></button>' },
          Teleport: true,
          TripCard: {
            props: ['order'],
            template: '<article class="trip-card">{{ order.status }}</article>',
          },
        },
      },
    })

    await flushPromises()

    const exceptionTab = wrapper.findAll('.tabs button').find((button) => button.text() === 'exception')
    expect(exceptionTab).toBeTruthy()

    await exceptionTab.trigger('click')
    await flushPromises()

    expect(getMock).toHaveBeenCalledWith('/ride-orders', {
      params: { status: 'exception', per_page: 30 },
    })

    const cards = wrapper.findAll('.trip-card')
    expect(cards).toHaveLength(1)
    expect(cards[0].text()).toContain('exception')
  })
})
