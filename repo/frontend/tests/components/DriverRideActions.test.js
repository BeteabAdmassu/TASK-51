import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import DriverRideActions from '@/components/driver/DriverRideActions.vue'

describe('DriverRideActions', () => {
  it('shows contextual action buttons by status', () => {
    const accepted = mount(DriverRideActions, {
      props: {
        ride: { status: 'accepted' },
      },
      global: {
        stubs: { Teleport: true },
      },
    })

    expect(accepted.text()).toContain('Start Trip')
    expect(accepted.text()).not.toContain('Complete Trip')

    const inProgress = mount(DriverRideActions, {
      props: {
        ride: { status: 'in_progress' },
      },
      global: {
        stubs: { Teleport: true },
      },
    })

    expect(inProgress.text()).toContain('Complete Trip')
    expect(inProgress.text()).not.toContain('Start Trip')
  })

  it('opens exception modal and validates reason', async () => {
    const wrapper = mount(DriverRideActions, {
      props: {
        ride: { status: 'accepted' },
      },
      global: {
        stubs: { Teleport: true },
      },
    })

    await wrapper.findAll('button').find((button) => button.text().includes('Flag Exception')).trigger('click')
    await wrapper.vm.$nextTick()

    await wrapper.findAll('button').find((button) => button.text().includes('Submit')).trigger('click')
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Reason is required.')
  })
})
