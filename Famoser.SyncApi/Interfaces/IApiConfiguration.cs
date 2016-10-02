using System;
using System.Collections.Generic;
using System.Diagnostics.Contracts;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities;

namespace Famoser.SyncApi.Interfaces
{
    public interface IApiConfiguration
    {
        Uri GetApiUri();

        Task<Guid> GetUserIdAsync();
        Task<List<Guid>> GetGroupIdAsync(string groupIdentifier);
        Task AddGroupIdAsync(string groupIdentifier, Guid id);
    }
}
