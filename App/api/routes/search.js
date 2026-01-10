import supabase from '../_supabase.js'

export default async function handler(req, res) {
  try {
    const url = new URL(req.url, `http://${req.headers.host}`)
    const from = url.searchParams.get('from') || ''
    const to = url.searchParams.get('to') || ''

    // fetch routes with stops
    const { data: routes, error } = await supabase
      .from('transit_routes')
      .select('id, name, on_time_rate, stops(*)')

    if (error) return res.status(500).json({ error: error.message })

    // simple scoring: count matches in stop names
    const normalize = (s) => (s || '').toString().toLowerCase().trim()
    const f = normalize(from)
    const t = normalize(to)

    const results = (routes || []).map((r) => {
      const stops = (r.stops || [])
      let score = 0
      if (f) score += stops.filter((s) => s.name.toLowerCase().includes(f)).length
      if (t) score += stops.filter((s) => s.name.toLowerCase().includes(t)).length
      return { id: r.id, name: r.name, on_time_rate: r.on_time_rate, stops, match_score: score }
    })

    results.sort((a, b) => b.match_score - a.match_score)

    return res.status(200).json(results)
  } catch (err) {
    return res.status(500).json({ error: String(err) })
  }
}
