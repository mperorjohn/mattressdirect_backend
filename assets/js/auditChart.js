// Extract standardIds from session data
document.addEventListener('DOMContentLoaded', function() {
    // Get the myStandards data from the data attribute
    const myStandardsElement = document.getElementById('myStandards');
    const myStandards = myStandardsElement ? JSON.parse(myStandardsElement.dataset.standards) : [];
    const clientId = myStandardsElement ? myStandardsElement.dataset.clientId : null;

    // Extract standard IDs
    const standardIds = Object.values(myStandards).map(standard => standard.StandardId);

    // Prepare request body
    const body = {
        'standardId': standardIds,
        'clientId': clientId,
        'auditId': [] // This can be populated if needed
    };

    // Fetch compliance data
    fetch(complainceUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(body)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updateCharts(data.results);
        }
    })
    .catch(error => console.error('Error:', error));
});

function updateCharts(data) {
    // Update bar chart
    if (data.details && data.details.length > 0) {
        const standardNames = data.details.map(item => item.standardName);
        const compliantData = data.details.map(item => {
            const compliant = item.score.find(s => s.type === 'compliant');
            return compliant ? compliant.percentage : 0;
        });
        const partialData = data.details.map(item => {
            const partial = item.score.find(s => s.type === 'partial_compliant');
            return partial ? partial.percentage : 0;
        });
        const nonCompliantData = data.details.map(item => {
            const nonCompliant = item.score.find(s => s.type === 'non_compliant');
            return nonCompliant ? nonCompliant.percentage : 0;
        });

        // Update bar chart
        if (typeof chart !== 'undefined') {
            chart.updateOptions({
                xaxis: {
                    categories: standardNames
                },
                series: [{
                    name: "Compliant",
                    data: compliantData
                },
                {
                    name: "Partially Compliant",
                    data: partialData
                },
                {
                    name: "Non Compliant",
                    data: nonCompliantData
                }]
            });
        }

        // Update donut chart with aggregate data
        if (data.aggregate && typeof chartDonut !== 'undefined') {
            chartDonut.updateSeries([
                data.aggregate.find(item => item.type === 'compliant')?.percentage || 0,
                data.aggregate.find(item => item.type === 'partial_compliant')?.percentage || 0,
                data.aggregate.find(item => item.type === 'non_compliant')?.percentage || 0
            ]);
        }
    }
}
